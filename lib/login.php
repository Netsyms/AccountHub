<?php

/**
 * Authentication and account functions
 */
use Base32\Base32;
use OTPHP\TOTP;
use LdapTools\LdapManager;
use LdapTools\Object\LdapObjectType;

$ldap = new LdapManager($ldap_config);

////////////////////////////////////////////////////////////////////////////////
//                           Account handling                                 //
////////////////////////////////////////////////////////////////////////////////

/**
 * Add a user to the system.  /!\ Assumes input is OK /!\
 * @param string $username Username, saved in lowercase.
 * @param string $password Password, will be hashed before saving.
 * @param string $realname User's real legal name
 * @param string $email User's email address.
 * @param string $phone1 Phone number #1
 * @param string $phone2 Phone number #2
 * @param string $type Account type
 * @return int The new user's ID number in the database.
 */
function adduser($username, $password, $realname, $email = null, $phone1 = "", $phone2 = "", $type) {
    global $database;
    $database->insert('accounts', [
        'username' => strtolower($username),
        'password' => (is_null($password) ? null : encryptPassword($password)),
        'realname' => $realname,
        'email' => $email,
        'phone1' => $phone1,
        'phone2' => $phone2,
        'acctstatus' => 1,
        'accttype' => $type
    ]);
    //var_dump($database->error());
    return $database->id();
}

/**
 * Change the password for the current user.
 * @global $database $database
 * @global LdapManager $ldap
 * @param string $old The current password
 * @param string $new The new password
 * @param string $new2 New password again
 * @param [string] $error If the function returns false, this will have an array 
 * with a message ID from `lang/messages.php` and (depending on the message) an 
 * extra string for that message.
 * @return boolean true if the password is changed, else false
 */
function change_password($old, $new, $new2, &$error) {
    global $database, $ldap;
    // make sure the new password isn't the same as the current one
    if ($old == $new) {
        $error = ["passwords_same"];
        return false;
    }
    // Make sure the new passwords are the same
    if ($new != $new2) {
        $error = ["new_password_mismatch"];
        return false;
    }
    // check the current password
    $login_ok = authenticate_user($_SESSION['username'], $old, $errmsg, $errcode);
    // Allow login if the error is due to expired password
    if (!$login_ok && ($errcode == LdapTools\Connection\ADResponseCodes::ACCOUNT_PASSWORD_EXPIRED || $errcode == LdapTools\Connection\ADResponseCodes::ACCOUNT_PASSWORD_MUST_CHANGE)) {
        $login_ok = true;
    }
    if ($login_ok) {
        // Check the new password and make sure it's not stupid
        require_once __DIR__ . "/worst_passwords.php";
        $passrank = checkWorst500List($new);
        if ($passrank !== FALSE) {
            $error = ["password_500", $passrank];
            return false;
        }
        if (strlen($new) < MIN_PASSWORD_LENGTH) {
            $error = ["weak_password"];
            return false;
        }

        // Figure out how to change the password, then do it
        $acctloc = account_location($_SESSION['username']);
        if ($acctloc == "LOCAL") {
            $database->update('accounts', ['password' => encryptPassword($new), 'acctstatus' => 1], ['uid' => $_SESSION['uid']]);
            $_SESSION['password'] = $new;
            insertAuthLog(3, $_SESSION['uid']);
            return true;
        } else if ($acctloc == "LDAP") {
            try {
                $repository = $ldap->getRepository(LdapObjectType::USER);
                $user = $repository->findOneByUsername($_SESSION['username']);
                $user->setPassword($new);
                $user->setpasswordMustChange(false);
                $ldap->persist($user);
                $database->update('accounts', ['acctstatus' => 1], ['uid' => $_SESSION['uid']]);
                insertAuthLog(3, $_SESSION['uid']);
                $_SESSION['password'] = $new;
                return true;
            } catch (\Exception $e) {
                // Stupid password complexity BS error
                if (strpos($e->getMessage(), "DSID-031A11E5") !== FALSE) {
                    $error = ["password_complexity"];
                    return false;
                }
                $error = ["ldap_error", $e->getMessage()];
                return false;
            }
        }
        $error = ["account_state_error"];
        return false;
    }
    $error = ["old_password_mismatch"];
    return false;
}

/**
 * Get where a user's account actually is.
 * @param string $username
 * @return string "LDAP", "LOCAL", "LDAP_ONLY", or "NONE".
 */
function account_location($username) {
    global $database;
    $username = strtolower($username);
    $user_exists = user_exists_local($username);
    if (!$user_exists && !LDAP_ENABLED) {
        return false;
    }
    if ($user_exists) {
        $userinfo = $database->select('accounts', ['password'], ['username' => $username])[0];
        // if password empty, it's an LDAP user
        if (is_empty($userinfo['password']) && LDAP_ENABLED) {
            return "LDAP";
        } else if (is_empty($userinfo['password']) && !LDAP_ENABLED) {
            return "NONE";
        } else {
            return "LOCAL";
        }
    } else {
        if (user_exists_ldap($username)) {
            return "LDAP_ONLY";
        } else {
            return "NONE";
        }
    }
}

/**
 * Checks the given credentials against the database.
 * @param string $username
 * @param string $password
 * @return boolean True if OK, else false
 */
function authenticate_user($username, $password, &$errormsg, &$errorcode) {
    global $database;
    global $ldap;
    $username = strtolower($username);
    if (is_empty($username) || is_empty($password)) {
        return false;
    }
    $loc = account_location($username, $password);
    switch ($loc) {
        case "LOCAL":
            $hash = $database->select('accounts', ['password'], ['username' => $username, "LIMIT" => 1])[0]['password'];
            return (comparePassword($password, $hash));
        case "LDAP":
            return authenticate_user_ldap($username, $password, $errormsg, $errorcode) === TRUE;
        case "LDAP_ONLY":
            // Authenticate with LDAP and create database account
            try {
                if (authenticate_user_ldap($username, $password, $errormsg, $errorcode) === TRUE) {
                    $user = $ldap->getRepository('user')->findOneByUsername($username);

                    adduser($user->getUsername(), null, $user->getName(), ($user->hasEmailAddress() ? $user->getEmailAddress() : null), "", "", 2);
                    return true;
                }
                return false;
            } catch (Exception $e) {
                $errormsg = $e->getMessage();
                return false;
            }
        default:
            return false;
    }
}

function user_exists($username) {
    return account_location(strtolower($username)) !== "NONE";
}

/**
 * Check if a username exists in the local database.
 * @param String $username
 */
function user_exists_local($username) {
    global $database;
    $username = strtolower($username);
    return $database->has('accounts', ['username' => $username, "LIMIT" => QUERY_LIMIT]);
}

/**
 * Get the account status: NORMAL, TERMINATED, LOCKED_OR_DISABLED,
 * CHANGE_PASSWORD, ALERT_ON_ACCESS, or OTHER
 * @global $database $database
 * @param string $username
 * @param string $password
 * @return string
 */
function get_account_status($username, &$error) {
    global $database;
    $username = strtolower($username);
    $loc = account_location($username);
    if ($loc == "LOCAL") {
        $statuscode = $database->select('accounts', [
                    '[>]acctstatus' => [
                        'acctstatus' => 'statusid'
                    ]
                        ], [
                    'accounts.acctstatus',
                    'acctstatus.statuscode'
                        ], [
                    'username' => $username,
                    "LIMIT" => 1
                        ]
                )[0]['statuscode'];
        return $statuscode;
    } else if ($loc == "LDAP" || $loc == "LDAP_ONLY") {
        return get_account_status_ldap($username, $error);
    } else {
        // account isn't setup properly
        return "OTHER";
    }
}

/**
 * Check if the given username has the given permission (or admin access)
 * @global $database $database
 * @param string $username
 * @param string $permcode
 * @return boolean TRUE if the user has the permission (or admin access), else FALSE
 */
function account_has_permission($username, $permcode) {
    global $database;
    return $database->has('assigned_permissions', [
                '[>]accounts' => [
                    'uid' => 'uid'
                ],
                '[>]permissions' => [
                    'permid' => 'permid'
                ]
                    ], ['AND' => ['OR' => ['permcode #code' => $permcode, 'permcode #admin' => 'ADMIN'], 'username' => $username]]) === TRUE;
}

////////////////////////////////////////////////////////////////////////////////
//                              Login handling                                //
////////////////////////////////////////////////////////////////////////////////

/**
 * Setup $_SESSION values to log in a user
 * @param string $username
 */
function doLoginUser($username, $password) {
    global $database;
    $username = strtolower($username);
    $userinfo = $database->select('accounts', ['email', 'uid', 'realname'], ['username' => $username])[0];
    $_SESSION['username'] = $username;
    $_SESSION['uid'] = $userinfo['uid'];
    $_SESSION['email'] = $userinfo['email'];
    $_SESSION['realname'] = $userinfo['realname'];
    $_SESSION['password'] = $password; // needed for accessing data in other apps
    $_SESSION['loggedin'] = true;
}

/**
 * Send an alert email to the system admin
 * 
 * Used when an account with the status ALERT_ON_ACCESS logs in
 * @param String $username the account username
 * @return Mixed TRUE if successful, error string if not
 */
function sendLoginAlertEmail($username, $appname = SITE_TITLE) {
    if (is_empty(ADMIN_EMAIL) || filter_var(ADMIN_EMAIL, FILTER_VALIDATE_EMAIL) === FALSE) {
        return "invalid_to_email";
    }
    if (is_empty(FROM_EMAIL) || filter_var(FROM_EMAIL, FILTER_VALIDATE_EMAIL) === FALSE) {
        return "invalid_from_email";
    }

    $username = strtolower($username);
    
    $mail = new PHPMailer;

    if (DEBUG) {
        $mail->SMTPDebug = 2;
    }

    if (USE_SMTP) {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = SMTP_AUTH;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;
        if (SMTP_ALLOW_INVALID_CERTIFICATE) {
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
        }
    }

    $mail->setFrom(FROM_EMAIL, 'Account Alerts');
    $mail->addAddress(ADMIN_EMAIL, "System Admin");
    $mail->isHTML(false);
    $mail->Subject = lang("admin alert email subject", false);
    $mail->Body = lang2("admin alert email message", ["username" => $username, "datetime" => date("Y-m-d H:i:s"), "ipaddr" => getClientIP(), "appname" => $appname], false);

    if (!$mail->send()) {
        return $mail->ErrorInfo;
    }
    return TRUE;
}

function insertAuthLog($type, $uid = null, $data = "") {
    global $database;
    // find IP address
    $ip = getClientIP();
    $database->insert("authlog", ['logtime' => date("Y-m-d H:i:s"), 'logtype' => $type, 'uid' => $uid, 'ip' => $ip, 'otherdata' => $data]);
}

function verifyReCaptcha($response) {
    try {
        $client = new GuzzleHttp\Client();

        $response = $client
                ->request('POST', "https://www.google.com/recaptcha/api/siteverify", [
            'form_params' => [
                'secret' => RECAPTCHA_SECRET_KEY,
                'response' => $response
            ]
        ]);

        if ($response->getStatusCode() != 200) {
            return false;
        }

        $resp = json_decode($response->getBody(), TRUE);
        if ($resp['success'] === true) {
            return true;
        } else {
            return false;
        }
    } catch (Exception $e) {
        return false;
    }
}

////////////////////////////////////////////////////////////////////////////////
//                              LDAP handling                                 //
////////////////////////////////////////////////////////////////////////////////

/**
 * Checks the given credentials against the LDAP server.
 * @param string $username
 * @param string $password
 * @return mixed True if OK, else false or the error code from the server
 */
function authenticate_user_ldap($username, $password, &$errormsg, &$errorcode) {
    global $ldap;
    if (is_empty($username) || is_empty($password)) {
        return false;
    }
    $username = strtolower($username);
    try {
        $msg = "";
        $code = 0;
        if ($ldap->authenticate($username, $password, $msg, $code) === TRUE) {
            $errormsg = $msg;
            $errorcode = $code;
            return true;
        } else {
            $errormsg = $msg;
            $errorcode = $code;
            return $msg;
        }
    } catch (Exception $e) {
        $errormsg = $e->getMessage();
        return $e->getMessage();
    }
}

/**
 * Check if a username exists on the LDAP server.
 * @global type $ldap_config
 * @param type $username
 * @return boolean true if yes, else false
 */
function user_exists_ldap($username) {
    global $ldap;
    try {
        $username = strtolower($username);
        $lqb = $ldap->buildLdapQuery();
        $result = $lqb->fromUsers()
                ->where(['username' => $username])
                ->getLdapQuery()
                ->getResult();
        if (count($result) > 0) {
            return true;
        }
        return false;
    } catch (Exception $e) {
        return false;
    }
}

function get_account_status_ldap($username, &$error) {
    global $ldap;
    try {
        $username = strtolower($username);
        $normal = $ldap->buildLdapQuery()
                ->fromUsers()
                ->where(['enabled' => true, 'passwordMustChange' => false, 'locked' => false, 'disabled' => false, 'username' => $username])
                ->getLdapQuery()
                ->getResult();
        if (count($normal) == 1) {
            return "NORMAL";
        }
        $disabled = $ldap->buildLdapQuery()
                ->fromUsers()
                ->where(['disabled' => true, 'username' => $username])
                ->getLdapQuery()
                ->getResult();
        $locked = $ldap->buildLdapQuery()
                ->fromUsers()
                ->where(['locked' => true, 'username' => $username])
                ->getLdapQuery()
                ->getResult();
        if (count($disabled) == 1 || count($locked) == 1) {
            return "LOCKED_OR_DISABLED";
        }
        $passwordExpired = $ldap->buildLdapQuery()
                ->fromUsers()
                ->where(['passwordMustChange' => true, 'username' => $username])
                ->getLdapQuery()
                ->getResult();
        if (count($passwordExpired) == 1) {
            return "CHANGE_PASSWORD";
        }
        $other = $ldap->buildLdapQuery()
                ->fromUsers()
                ->where(['username' => $username])
                ->getLdapQuery()
                ->getResult();
        if (count($other) == 0) {
            return false;
        } else {
            return "OTHER";
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
        return false;
    }
}

////////////////////////////////////////////////////////////////////////////////
//                          2-factor authentication                           //
////////////////////////////////////////////////////////////////////////////////

/**
 * Check if a user has TOTP setup
 * @global $database $database
 * @param string $username
 * @return boolean true if TOTP secret exists, else false
 */
function userHasTOTP($username) {
    global $database;
    $username = strtolower($username);
    $secret = $database->select('accounts', 'authsecret', ['username' => $username])[0];
    if (is_empty($secret)) {
        return false;
    }
    return true;
}

/**
 * Generate a TOTP secret for the given user.
 * @param string $username
 * @return string OTP provisioning URI (for generating a QR code)
 */
function newTOTP($username) {
    global $database;
    $username = strtolower($username);
    $secret = random_bytes(20);
    $encoded_secret = Base32::encode($secret);
    $userdata = $database->select('accounts', ['email', 'authsecret', 'realname'], ['username' => $username])[0];
    $totp = new TOTP((is_null($userdata['email']) ? $userdata['realname'] : $userdata['email']), $encoded_secret);
    $totp->setIssuer(SYSTEM_NAME);
    return $totp->getProvisioningUri();
}

/**
 * Save a TOTP secret for the user.
 * @global $database $database
 * @param string $username
 * @param string $secret
 */
function saveTOTP($username, $secret) {
    global $database;
    $username = strtolower($username);
    $database->update('accounts', ['authsecret' => $secret], ['username' => $username]);
}

/**
 * Verify a TOTP multiauth code
 * @global $database
 * @param string $username
 * @param int $code
 * @return boolean true if it's legit, else false
 */
function verifyTOTP($username, $code) {
    global $database;
    $username = strtolower($username);
    $userdata = $database->select('accounts', ['email', 'authsecret'], ['username' => $username])[0];
    if (is_empty($userdata['authsecret'])) {
        return false;
    }
    $totp = new TOTP(null, $userdata['authsecret']);
    return $totp->verify($code);
}
