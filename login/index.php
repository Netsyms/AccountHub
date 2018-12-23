<?php

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

require_once __DIR__ . "/../required.php";


if (empty($_GET['code']) || empty($_GET['redirect'])) {
    die("Bad request.");
}

// Delete old keys to keep the table small and tidy
$database->delete("userloginkeys", ["expires[<]" => date("Y-m-d H:i:s")]);

if (!$database->has("userloginkeys", ["AND" => ["key" => $_GET["code"]], "expires[>]" => date("Y-m-d H:i:s"), "uid" => null])) {
    header("Location: $_GET[redirect]");
    die("Invalid auth code.");
}

$APPINFO = $database->get("userloginkeys", ["appname", "appicon"], ["key" => $_GET["code"]]);
$APPNAME = $APPINFO["appname"];
$APPICON = $APPINFO["appicon"];

if (empty($_SESSION['thisstep'])) {
    $_SESSION['thisstep'] = "username";
}

$error = "";

function sendUserBack($code, $url, $uid) {
    global $database;
    $_SESSION['check'] = null;
    $_SESSION['thisstep'] = null;
    $_SESSION['login_uid'] = null;
    $_SESSION['login_code'] = null;
    $_SESSION['login_pwd'] = null;
    $database->update("userloginkeys", ["uid" => $uid], ["key" => $code]);
    header("Location: $url");
    die("<a href=\"" . htmlspecialchars($url) . "\">Click here</a>");
}

if (!empty($_SESSION['check'])) {
    switch ($_SESSION['check']) {
        case "username":
            if (empty($_POST['username'])) {
                $_SESSION['thisstep'] = "username";
                break;
            }
            $user = User::byUsername($_POST['username']);
            if ($user->exists()) {
                $_SESSION['login_uid'] = $user->getUID();
                switch ($user->getStatus()->get()) {
                    case AccountStatus::LOCKED_OR_DISABLED:
                        $error = $Strings->get("account locked", false);
                        break;
                    case AccountStatus::TERMINATED:
                        $error = $Strings->get("account terminated", false);
                        break;
                    case AccountStatus::ALERT_ON_ACCESS:
                        $mail_resp = $user->sendAlertEmail();
                    case AccountStatus::NORMAL:
                        $_SESSION['thisstep'] = "password";
                        break;
                    case AccountStatus::CHANGE_PASSWORD:
                        $_SESSION['thisstep'] = "change_password";
                        break;
                }
            } else {
                $error = $Strings->get("Username not found.", false);
            }
            break;
        case "password":
            if (empty($_POST['password'])) {
                $_SESSION['thisstep'] = "password";
                break;
            }
            if (empty($_SESSION['login_uid'])) {
                $_SESSION['thisstep'] = "username";
                break;
            }
            $user = new User($_SESSION['login_uid']);
            if ($user->checkPassword($_POST['password'])) {
                $_SESSION['login_pwd'] = true;
                if ($user->has2fa()) {
                    $_SESSION['thisstep'] = "totp";
                } else {
                    sendUserBack($_GET['code'], $_GET['redirect'], $_SESSION['login_uid']);
                }
            } else {
                $error = $Strings->get("Password incorrect.", false);
            }
            break;
        case "change_password":
            if (empty($_POST['oldpassword']) || empty($_POST['newpassword']) || empty($_POST['newpassword2'])) {
                $_SESSION['thisstep'] = "change_password";
                $error = $Strings->get("Fill in all three boxes.", false);
                break;
            }

            $user = new User($_SESSION['login_uid']);
            try {
                $result = $user->changePassword($_POST['oldpassword'], $_POST['newpassword'], $_POST['newpassword2']);

                if ($result === TRUE) {
                    if ($user->has2fa()) {
                        $_SESSION['thisstep'] = "totp";
                    } else {
                        sendUserBack($_GET['code'], $_GET['redirect'], $_SESSION['login_uid']);
                    }
                }
            } catch (PasswordMatchException $e) {
                $error = $Strings->get(MESSAGES["passwords_same"]["string"], false);
            } catch (PasswordMismatchException $e) {
                $error = $Strings->get(MESSAGES["new_password_mismatch"]["string"], false);
            } catch (IncorrectPasswordException $e) {
                $error = $Strings->get(MESSAGES["old_password_mismatch"]["string"], false);
            } catch (WeakPasswordException $e) {
                $error = $Strings->get(MESSAGES["weak_password"]["string"], false);
            }
            break;
        case "totp":
            if (empty($_POST['totp']) || empty($_SESSION['login_uid'])) {
                $_SESSION['thisstep'] = "username";
                break;
            }
            $user = new User($_SESSION['login_uid']);
            if ($user->check2fa($_POST['totp'])) {
                sendUserBack($_GET['code'], $_GET['redirect'], $_SESSION['login_uid']);
            } else {
                $error = $Strings->get("Code incorrect.", false);
            }
            break;
    }
}

include __DIR__ . "/parts/header.php";

switch ($_SESSION['thisstep']) {
    case "username":
        require __DIR__ . "/parts/username.php";
        break;
    case "password":
        require __DIR__ . "/parts/password.php";
        break;
    case "change_password":
        require __DIR__ . "/parts/change_password.php";
        break;
    case "totp":
        require __DIR__ . "/parts/totp.php";
        break;
}

include __DIR__ . "/parts/footer.php";
