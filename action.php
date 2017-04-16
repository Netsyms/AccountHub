<?php

/**
 * Make things happen when buttons are pressed and forms submitted.
 */
require_once __DIR__ . "/required.php";

dieifnotloggedin();

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
        session_destroy();
        header('Location: index.php');
        die("Logged out.");
    case "chpasswd":
        $oldmatch = comparePassword($VARS['oldpass'], $database->select('accounts', 'password', ['uid' => $_SESSION['uid']])[0]);
        if ($oldmatch) {
            if ($VARS['newpass'] == $VARS['conpass']) {
                $passrank = checkWorst500List($VARS['newpass']);
                if ($passrank !== FALSE) {
                    returnToSender("password_500", $passrank);
                }
                if (strlen($VARS['newpass']) < MIN_PASSWORD_LENGTH) {
                    returnToSender("weak_password");
                }
                $database->update('accounts', ['password' => encryptPassword($VARS['newpass'])], ['uid' => $_SESSION['uid']]);
                returnToSender("password_updated");
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
        returnToSender("2fa_enabled");
    case "rm2fa":
        $database->update('accounts', ['authsecret' => ""], ['uid' => $_SESSION['uid']]);
        returnToSender("2fa_removed");
        break;
}