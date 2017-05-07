<?php

/**
 * Make things happen when buttons are pressed and forms submitted.
 */
use LdapTools\LdapManager;
use LdapTools\Object\LdapObjectType;

require_once __DIR__ . "/required.php";

// If the user presses Sign Out but we're not logged in anymore,
// we don't want to show a nasty error.
if ($VARS['action'] == 'signout' && $_SESSION['loggedin'] != true) {
    session_destroy();
    header('Location: index.php');
    die("Logged out (session was expired anyways).");
}

dieifnotloggedin();

require_once __DIR__ . "/lib/login.php";
require_once __DIR__ . "/lib/worst_passwords.php";

function returnToSender($msg, $arg = "") {
    global $VARS;
    if ($arg == "") {
        header("Location: home.php?page=" . urlencode($VARS['source']) . "&msg=$msg");
    } else {
        header("Location: home.php?page=" . urlencode($VARS['source']) . "&msg=$msg&arg=" . urlencode($arg));
    }
    die();
}

switch ($VARS['action']) {
    case "signout":
        insertAuthLog(11, $_SESSION['uid']);
        session_destroy();
        header('Location: index.php');
        die("Logged out.");
    case "chpasswd":
        if ($VARS['oldpass'] == $VARS['newpass']) {
            returnToSender("passwords_same");
        }
        if (authenticate_user($_SESSION['username'], $VARS['oldpass'])) {
            if ($VARS['newpass'] == $VARS['conpass']) {
                $passrank = checkWorst500List($VARS['newpass']);
                if ($passrank !== FALSE) {
                    returnToSender("password_500", $passrank);
                }
                if (strlen($VARS['newpass']) < MIN_PASSWORD_LENGTH) {
                    returnToSender("weak_password");
                }

                $acctloc = account_location($_SESSION['username'], $_SESSION['password']);

                if ($acctloc == "LOCAL") {
                    $database->update('accounts', ['password' => encryptPassword($VARS['newpass'])], ['uid' => $_SESSION['uid']]);
                    $_SESSION['password'] = $VARS['newpass'];
                    insertAuthLog(3, $_SESSION['uid']);
                    returnToSender("password_updated");
                } else if ($acctloc == "LDAP") {
                    /* $ldap_config_domain
                      ->setUsername($_SESSION['username'])
                      ->setPassword($VARS['oldpass']); */
                    try {
                        //echo "0";
                        $ldapManager = new LdapManager($ldap_config);
                        //echo "1";
                        $repository = $ldapManager->getRepository(LdapObjectType::USER);
                        //echo "2";
                        $user = $repository->findOneByUsername($_SESSION['username']);
                        //echo "3";
                        $user->setPassword($VARS['newpass']);
                        //echo "4";
                        $ldapManager->persist($user);
                        //echo "5";
                        insertAuthLog(3, $_SESSION['uid']);
                        $_SESSION['password'] = $VARS['newpass'];
                        returnToSender("password_updated");
                    } catch (\Exception $e) {
                        echo $e->getMessage();
                        returnToSender("ldap_error", $e->getMessage());
                    }
                } else {
                    returnToSender("account_state_error");
                }
            } else {
                returnToSender("new_password_mismatch");
            }
        } else {
            returnToSender("old_password_mismatch");
        }
        break;
    case "add2fa":
        if (is_empty($VARS['secret'])) {
            returnToSender("invalid_parameters");
        }
        $database->update('accounts', ['authsecret' => $VARS['secret']], ['uid' => $_SESSION['uid']]);
        insertAuthLog(9, $_SESSION['uid']);
        returnToSender("2fa_enabled");
    case "rm2fa":
        $database->update('accounts', ['authsecret' => ""], ['uid' => $_SESSION['uid']]);
        insertAuthLog(10, $_SESSION['uid']);
        returnToSender("2fa_removed");
        break;
}