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

function returnToSender($msg, $arg = "") {
    global $VARS;
    if ($arg == "") {
        header("Location: app.php?page=" . urlencode($VARS['source']) . "&msg=$msg");
    } else {
        header("Location: app.php?page=" . urlencode($VARS['source']) . "&msg=$msg&arg=" . urlencode($arg));
    }
    die();
}

switch ($VARS['action']) {
    case "signout":
        Log::insert(LogType::LOGOUT, $_SESSION['uid']);
        session_destroy();
        header('Location: index.php');
        die("Logged out.");
    case "chpasswd":
        engageRateLimit();
        $error = [];
        $user = new User($_SESSION['uid']);
        try {
            $result = $user->changePassword($VARS['oldpass'], $VARS['newpass'], $VARS['conpass']);

            if ($result === TRUE) {
                returnToSender("password_updated");
            }
        } catch (PasswordMatchException $e) {
            returnToSender("passwords_same");
        } catch (PasswordMismatchException $e) {
            returnToSender("new_password_mismatch");
        } catch (IncorrectPasswordException $e) {
            returnToSender("old_password_mismatch");
        } catch (WeakPasswordException $e) {
            returnToSender("weak_password");
        }
        break;
    case "chpin":
        engageRateLimit();
        $error = [];
        if (!($VARS['newpin'] == "" || (is_numeric($VARS['newpin']) && strlen($VARS['newpin']) >= 1 && strlen($VARS['newpin']) <= 8))) {
            returnToSender("invalid_pin_format");
        }
        if ($VARS['newpin'] == $VARS['conpin']) {
            $database->update('accounts', ['pin' => ($VARS['newpin'] == "" ? null : $VARS['newpin'])], ['uid' => $_SESSION['uid']]);
            returnToSender("pin_updated");
        }
        returnToSender("new_pin_mismatch");
        break;
    case "add2fa":
        if (is_empty($VARS['secret'])) {
            returnToSender("invalid_parameters");
        }
        $user = new User($_SESSION['uid']);
        $totp = new TOTP(null, $VARS['secret']);
        if (!$totp->verify($VARS["totpcode"])) {
            returnToSender("2fa_wrong_code");
        }
        $user->save2fa($VARS['secret']);
        Log::insert(LogType::ADDED_2FA, $user);
        returnToSender("2fa_enabled");
    case "rm2fa":
        engageRateLimit();
        (new User($_SESSION['uid']))->save2fa("");
        Log::insert(LogType::REMOVED_2FA, $_SESSION['uid']);
        returnToSender("2fa_removed");
        break;
    case "readnotification":
        $user = new User($_SESSION['uid']);

        if (empty($VARS['id'])) {
            returnToSender("invalid_parameters#notifications");
        }
        try {
            Notifications::read($user, $VARS['id']);
            returnToSender("#notifications");
        } catch (Exception $ex) {
            returnToSender("invalid_parameters#notifications");
        }
        break;
    case "deletenotification":
        $user = new User($_SESSION['uid']);

        if (empty($VARS['id'])) {
            returnToSender("invalid_parameters#notifications");
        }
        try {
            Notifications::delete($user, $VARS['id']);
            returnToSender("notification_deleted#notifications");
        } catch (Exception $ex) {
            returnToSender("invalid_parameters#notifications");
        }
        break;
}