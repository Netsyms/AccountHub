<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

require_once __DIR__ . "/required.php";

// If we're logged in, we don't need to be here.
if (!empty($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: home.php');
    die();
// This branch will likely run if the user signed in from a different app.
}

/* Authenticate user */
$username_ok = false;
$multiauth = false;
$change_password = false;
if (empty($VARS['progress'])) {
    // Easy way to remove "undefined" warnings.
} else if ($VARS['progress'] == "1") {
    engageRateLimit();
    if (!CAPTCHA_ENABLED || (CAPTCHA_ENABLED && Login::verifyCaptcha($VARS['captcheck_session_code'], $VARS['captcheck_selected_answer'], CAPTCHA_SERVER . "/api.php"))) {
        $autherror = "";
        $user = User::byUsername($VARS['username']);
        if ($user->exists()) {
            $status = $user->getStatus()->getString();
            switch ($status) {
                case "LOCKED_OR_DISABLED":
                    $alert = $Strings->get("account locked", false);
                    break;
                case "TERMINATED":
                    $alert = $Strings->get("account terminated", false);
                    break;
                case "CHANGE_PASSWORD":
                    $alert = $Strings->get("password expired", false);
                    $alerttype = "info";
                    $_SESSION['username'] = $user->getUsername();
                    $_SESSION['uid'] = $user->getUID();
                    $change_password = true;
                    break;
                case "NORMAL":
                    $username_ok = true;
                    break;
                case "ALERT_ON_ACCESS":
                    $mail_resp = $user->sendAlertEmail();
                    if (DEBUG) {
                        var_dump($mail_resp);
                    }
                    $username_ok = true;
                    break;
                default:
                    if (!empty($error)) {
                        $alert = $error;
                    } else {
                        $alert = $Strings->get("login error", false);
                    }
                    break;
            }
            if ($username_ok) {
                if ($user->checkPassword($VARS['password'])) {
                    $_SESSION['passok'] = true; // stop logins using only username and authcode
                    if ($user->has2fa()) {
                        $multiauth = true;
                    } else {
                        Session::start($user);
                        Log::insert(LogType::LOGIN_OK, $user->getUID());
                        header('Location: app.php');
                        die("Logged in, go to app.php");
                    }
                } else {
                    $alert = $Strings->get("login incorrect", false);
                    Log::insert(LogType::LOGIN_FAILED, null, "Username: " . $VARS['username']);
                }
            }
        } else { // User does not exist anywhere
            $alert = $Strings->get("login incorrect", false);
            Log::insert(LogType::LOGIN_FAILED, null, "Username: " . $VARS['username']);
        }
    } else {
        $alert = $Strings->get("captcha error", false);
        Log::insert(LogType::BAD_CAPTCHA, null, "Username: " . $VARS['username']);
    }
} else if ($VARS['progress'] == "2") {
    engageRateLimit();
    $user = User::byUsername($VARS['username']);
    if ($_SESSION['passok'] !== true) {
        // stop logins using only username and authcode
        sendError("Password integrity check failed!");
    }
    if ($user->check2fa($VARS['authcode'])) {
        Session::start($user);
        Log::insert(LogType::LOGIN_OK, $user->getUID());
        header('Location: app.php');
        die("Logged in, go to app.php");
    } else {
        $alert = $Strings->get("2fa incorrect", false);
        Log::insert(LogType::BAD_2FA, null, "Username: " . $VARS['username']);
    }
} else if ($VARS['progress'] == "chpasswd") {
    engageRateLimit();
    if (!empty($_SESSION['username'])) {
        $user = User::byUsername($_SESSION['username']);

        try {
            $result = $user->changePassword($VARS['oldpass'], $VARS['newpass'], $VARS['conpass']);

            if ($result === TRUE) {
                $alert = $Strings->get(MESSAGES["password_updated"]["string"], false);
                $alerttype = MESSAGES["password_updated"]["type"];
            }
        } catch (PasswordMatchException $e) {
            $alert = $Strings->get(MESSAGES["passwords_same"]["string"], false);
            $alerttype = "danger";
        } catch (PasswordMismatchException $e) {
            $alert = $Strings->get(MESSAGES["new_password_mismatch"]["string"], false);
            $alerttype = "danger";
        } catch (IncorrectPasswordException $e) {
            $alert = $Strings->get(MESSAGES["old_password_mismatch"]["string"], false);
            $alerttype = "danger";
        } catch (WeakPasswordException $e) {
            $alert = $Strings->get(MESSAGES["weak_password"]["string"], false);
            $alerttype = "danger";
        }
    } else {
        session_destroy();
        header('Location: index.php');
        die();
    }
}

header("Link: <static/fonts/Roboto.css>; rel=preload; as=style", false);
header("Link: <static/css/bootstrap.min.css>; rel=preload; as=style", false);
header("Link: <static/css/material-color/material-color.min.css>; rel=preload; as=style", false);
header("Link: <static/css/index.css>; rel=preload; as=style", false);
header("Link: <static/js/jquery-3.3.1.min.js>; rel=preload; as=script", false);
header("Link: <static/js/bootstrap.bundle.min.js>; rel=preload; as=script", false);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title><?php echo SITE_TITLE; ?></title>

        <link rel="icon" href="static/img/logo.svg">

        <link href="static/css/bootstrap.min.css" rel="stylesheet">
        <link href="static/css/material-color/material-color.min.css" rel="stylesheet">
        <link href="static/css/index.css" rel="stylesheet">
        <?php if (CAPTCHA_ENABLED) { ?>
            <script src="<?php echo CAPTCHA_SERVER ?>/captcheck.dist.js"></script>
<?php } ?>
    </head>
    <body>
        <div class="row justify-content-center">
            <div class="col-auto">
                <img class="banner-image" src="static/img/logo.svg" />
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="card col-11 col-xs-11 col-sm-8 col-md-6 col-lg-4">
                <div class="card-body">
                    <h5 class="card-title"><?php $Strings->get("sign in"); ?></h5>
                    <form action="" method="POST">
                        <?php
                        if (!empty($alert)) {
                            $alerttype = isset($alerttype) ? $alerttype : "danger";
                            ?>
                            <div class="alert alert-<?php echo $alerttype ?>">
                                <?php
                                switch ($alerttype) {
                                    case "danger":
                                        $alerticon = "fas fa-times";
                                        break;
                                    case "warning":
                                        $alerticon = "fas fa-exclamation-triangle";
                                        break;
                                    case "info":
                                        $alerticon = "fas fa-info-circle";
                                        break;
                                    case "success":
                                        $alerticon = "fas fa-check";
                                        break;
                                    default:
                                        $alerticon = "far fa-square";
                                }
                                ?>
                                <i class="<?php echo $alerticon ?> fa-fw"></i> <?php echo $alert ?>
                            </div>
                            <?php
                        }

                        if (!$multiauth && !$change_password) {
                            ?>
                            <input type="text" class="form-control" name="username" placeholder="<?php $Strings->get("username"); ?>" required="required" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" autofocus /><br />
                            <input type="password" class="form-control" name="password" placeholder="<?php $Strings->get("password"); ?>" required="required" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" /><br />
    <?php if (CAPTCHA_ENABLED) { ?>
                                <div class="captcheck_container" data-stylenonce="<?php echo $SECURE_NONCE; ?>"></div>
                                <br />
                            <?php } ?>
                            <input type="hidden" name="progress" value="1" />
                            <?php
                        } else if ($multiauth) {
                            ?>
                            <div class="alert alert-info">
    <?php $Strings->get("2fa prompt"); ?>
                            </div>
                            <input type="text" class="form-control" name="authcode" placeholder="<?php $Strings->get("authcode"); ?>" required="required" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" autofocus /><br />
                            <input type="hidden" name="progress" value="2" />
                            <input type="hidden" name="username" value="<?php echo $VARS['username']; ?>" />
                            <?php
                        } else if ($change_password) {
                            ?>
                            <input type="password" class="form-control" name="oldpass" placeholder="Current password" required="required" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" autofocus /><br />
                            <input type="password" class="form-control" name="newpass" placeholder="New password" required="required" autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" /><br />
                            <input type="password" class="form-control" name="conpass" placeholder="New password (again)" required="required" autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" /><br />
                            <input type="hidden" name="progress" value="chpasswd" />
                            <?php
                        }
                        ?>
                        <button type="submit" class="btn btn-primary">
<?php $Strings->get("continue"); ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="footer">
<?php echo FOOTER_TEXT; ?><br />
            Copyright &copy; <?php echo date('Y'); ?> <?php echo COPYRIGHT_NAME; ?>
        </div>
    </div>
    <script src="static/js/jquery-3.3.1.min.js"></script>
    <script src="static/js/bootstrap.bundle.min.js"></script>
</body>
</html>