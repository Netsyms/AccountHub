<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

/**
 * Make things happen when buttons are pressed and forms submitted.
 */
require_once __DIR__ . "/required.php";

use OTPHP\TOTP;

// If the user presses Sign Out but we're not logged in anymore,
// we don't want to show a nasty error.
if ($VARS['action'] == 'signout' && $_SESSION['loggedin'] != true) {
    session_destroy();
    header('Location: index.php');
    die("Logged out (session was expired anyways).");
}

dieifnotloggedin();

engageRateLimit();

require_once __DIR__ . "/lib/login.php";

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
        $error = [];
        $result = change_password($VARS['oldpass'], $VARS['newpass'], $VARS['conpass'], $error);
        if ($result === TRUE) {
            returnToSender("password_updated");
        }
        switch (count($error)) {
            case 1:
                returnToSender($error[0]);
            case 2:
                returnToSender($error[0], $error[1]);
            default:
                returnToSender("generic_op_error");
        }
        break;
    case "add2fa":
        if (is_empty($VARS['secret'])) {
            returnToSender("invalid_parameters");
        }
        $totp = new TOTP(null, $VARS['secret']);
        if (!$totp->verify($VARS["totpcode"])) {
            returnToSender("2fa_wrong_code");
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