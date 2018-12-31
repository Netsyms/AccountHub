<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */


require __DIR__ . '/../required.php';

if ($SETTINGS['signups_enabled'] !== true) {
    http_response_code(403);
    die("Account creation not allowed.  Contact the site administrator for an account.");
}

function showHTML($errormsg = null, $genform = true, $noformcontent = "", $title = null) {
    global $SETTINGS, $SECURE_NONCE, $Strings;
    $form = new FormBuilder("", "", "", "POST");

    $form->setID("signupform");

    $form->addInput("username", "", "text", true, null, null, "Username", "fas fa-id-card", 6, 4, 100, "[a-zA-Z0-9]+", $Strings->get("Please enter your username (4-100 characters, alphanumeric).", false));
    $form->addInput("password", "", "password", true, null, null, "Password", "fas fa-lock", 6, $SETTINGS['min_password_length'], 255, "", $Strings->build("Your password must be at least {n} characters long.", ["n" => $SETTINGS['min_password_length']], false));
    $form->addInput("email", "", "email", false, null, null, "Email", "fas fa-envelope", 6, 5, 255, "", $Strings->get("That email address doesn't look right.", false));
    $form->addInput("name", "", "text", true, null, null, "Name", "fas fa-user", 6, 2, 200, "", $Strings->get("Enter your name.", false));

    $form->addHiddenInput("submit", "1");

    $form->addButton("Create Account", "fas fa-user-plus", null, "submit", "savebtn");
    ?>
    <!DOCTYPE html>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?php echo $SETTINGS['site_title']; ?></title>

    <link rel="icon" href="../static/img/logo.svg">

    <link href="../static/css/bootstrap.min.css" rel="stylesheet">
    <link href="../static/css/svg-with-js.min.css" rel="stylesheet">
    <script nonce="<?php echo $SECURE_NONCE; ?>">
        FontAwesomeConfig = {autoAddCss: false}
    </script>
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
                <img class="banner-image" src="../static/img/logo.svg" />
            </div>

            <div class="col-12 text-center">
                <h1 class="display-5 mb-4"><?php
                    if (is_null($title)) {
                        $Strings->get("Create Account");
                    } else {
                        echo $title;
                    }
                    ?></h1>
            </div>

            <div class="col-12 col-sm-8">
                <div class="mt-4">
                    <?php
                    if (!is_null($errormsg)) {
                        ?>
                        <div class="alert alert-danger">
                            <?php echo $errormsg; ?>
                        </div>
                        <?php
                    }

                    if ($genform) {
                        $form->generate();
                    } else {
                        echo $noformcontent;
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <script src="../static/js/fontawesome-all.min.js"></script>
    <script src="../static/js/jquery-3.3.1.min.js"></script>
    <script nonce="<?php echo $SECURE_NONCE; ?>">
        $("#savebtn").click(function (event) {
            var form = $("#signupform");

            if (form[0].checkValidity() === false) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.addClass('was-validated');
        });
    </script>
    <?php
    die();
}

// If we didn't submit the form yet
if (empty($_POST['submit'])) {
    showHTML();
}


// Validation
if (empty($_POST['username'])) {
    showHTML($Strings->get("Choose a username.", false));
}
$_POST['username'] = strtolower($_POST['username']);
if (!preg_match("/^[a-z0-9]+$/", $_POST['username'])) {
    showHTML($Strings->get("Please enter your username (4-100 characters, alphanumeric).", false));
}
if (User::byUsername($_POST['username'])->exists()) {
    showHTML($Strings->get("Username already taken, pick another.", false));
}
if (empty($_POST['password'])) {
    showHTML($Strings->get("Choose a password.", false));
}
if (strlen($_POST['password']) < $SETTINGS['min_password_length']) {
    showHTML($Strings->build("Your password must be at least {n} characters long.", ["n" => $SETTINGS[min_password_length]], false));
}
require_once __DIR__ . "/../lib/worst_passwords.php";
$passrank = checkWorst500List($new);
if ($passrank !== FALSE) {
    showHTML($Strings->get("That password is one of the most popular and insecure ever, make a better one.", false));
}
if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    showHTML($Strings->get("That email address doesn't look right.", false));
}
if (empty($_POST['name'])) {
    showHTML($Strings->get("Enter your name.", false));
}

// Create account

$userid = User::add($_POST['username'], $_POST['password'], $_POST['name'], (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ? $_POST['email'] : null));
$signinstr = $Strings->get("sign in", false);
showHTML(null, false, <<<END
<div class="card mt-4">
    <div class="card-body">
        <a href="../" class="btn btn-primary btn-block">$signinstr</a>
    </div>
</div>
END
        , $Strings->get("Account Created", false));
