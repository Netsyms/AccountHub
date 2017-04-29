<?php

/**
 * Make things happen when buttons are pressed and forms submitted.
 */
use LdapTools\LdapManager;
use LdapTools\Object\LdapObjectType;

require_once __DIR__ . "/required.php";

dieifnotloggedin();

require_once __DIR__ . "/lib/login.php";
require_once __DIR__ . "/lib/worst_passwords.php";

function returnToSender($msg, $arg = "") {
    global $VARS;
    if ($arg == "") {
        header("Location: home.php?page=" . urlencode($VARS['source']) . "&msg=" . $msg);
    } else {
        header("Location: home.php?page=" . urlencode($VARS['source']) . "&msg=$msg&arg=$arg");
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
        if ($_SESSION['password'] == $VARS['oldpass']) {
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
                    $ldapManager = new LdapManager($ldap_config);
                    $repository = $ldapManager->getRepository(LdapObjectType::USER);
                    $user = $repository->findOneByUsername($_SESSION['username']);
                    $user->setPassword($VARS['newpass']);
                    try {
                        $ldapManager->persist($user);
                        insertAuthLog(3, $_SESSION['uid']);
                        returnToSender("password_updated");
                    } catch (\Exception $e) {
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