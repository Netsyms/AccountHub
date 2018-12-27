<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

require_once __DIR__ . "/required.php";

// if we're logged in, we don't need to be here.
if (!empty($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && !isset($_GET['permissionerror'])) {
    header('Location: app.php');
    die();
}


/**
 * Show a simple HTML page with a line of text and a button.  Matches the UI of
 * the AccountHub login flow.
 *
 * @global type $SETTINGS
 * @global type $SECURE_NONCE
 * @global type $Strings
 * @param string $title Text to show, passed through i18n
 * @param string $button Button text, passed through i18n
 * @param string $url URL for the button
 */
function showHTML(string $title, string $button, string $url) {
    global $SETTINGS, $SECURE_NONCE, $Strings;
    ?>
    <!DOCTYPE html>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?php echo $SETTINGS['site_title']; ?></title>

    <link rel="icon" href="static/img/logo.svg">

    <link href="static/css/bootstrap.min.css" rel="stylesheet">
    <style nonce="<?php echo $SECURE_NONCE; ?>">
        .display-5 {
            font-size: 2.5rem;
            font-weight: 300;
            line-height: 1.2;
        }

        .banner-image {
            max-height: 100px;
            margin: 2em auto;
            border: 1px solid grey;
            border-radius: 15%;
        }
    </style>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-12 text-center">
                <img class="banner-image" src="./static/img/logo.svg" />
            </div>

            <div class="col-12 text-center">
                <h1 class="display-5 mb-4"><?php $Strings->get($title); ?></h1>
            </div>

            <div class="col-12 col-sm-8 col-lg-6">
                <div class="card mt-4">
                    <div class="card-body">
                        <a href="<?php echo $url; ?>" class="btn btn-primary btn-block"><?php $Strings->get($button); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

if (!empty($_GET['logout'])) {
    showHTML("You have been logged out.", "Log in again", "./index.php");
    die();
}
if (empty($_SESSION["login_code"])) {
    $redirecttologin = true;
} else {
    try {
        $uid = LoginKey::getuid($_SESSION["login_code"]);

        $user = new User($uid);
        Session::start($user);
        $_SESSION["login_code"] = null;
        header('Location: app.php');
        showHTML("Logged in", "Continue", "./app.php");
        die();
    } catch (Exception $ex) {
        $redirecttologin = true;
    }
}

if ($redirecttologin) {
    try {
        $code = LoginKey::generate($SETTINGS["site_title"], "../static/img/logo.svg");

        $_SESSION["login_code"] = $code;

        $loginurl = "./login/?code=" . htmlentities($code) . "&redirect=" . htmlentities($_SERVER["REQUEST_URI"]);

        header("Location: $loginurl");
        showHTML("Continue", "Continue", $loginurl);
        die();
    } catch (Exception $ex) {
        sendError($ex->getMessage());
    }
}
