<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

header("Link: <../static/fonts/Roboto.css>; rel=preload; as=style", false);
header("Link: <../static/css/bootstrap.min.css>; rel=preload; as=style", false);
header("Link: <../static/css/login.css>; rel=preload; as=style", false);
header("Link: <../static/css/svg-with-js.min.css>; rel=preload; as=style", false);
header("Link: <../static/js/fontawesome-all.min.js>; rel=preload; as=script", false);
?>
<!DOCTYPE html>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title><?php echo $SETTINGS['site_title']; ?></title>

<link rel="icon" href="../static/img/logo.svg">

<link href="../static/css/bootstrap.min.css" rel="stylesheet">
<link href="../static/css/login.css" rel="stylesheet">
<link href="../static/css/svg-with-js.min.css" rel="stylesheet">

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-12 text-center">
            <h1 class="display-5 mb-4"><?php $Strings->build("Login to {app}", ["app" => htmlentities($APPNAME)]); ?></h1>
        </div>

        <div class="col-12 col-sm-8 col-lg-6">
            <div class="card mt-4">
                <div class="card-body">
                    <?php
                    if (!empty($error)) {
                        ?>
                        <div class="text-danger">
                            <?php echo $error; ?>
                        </div>
                        <?php
                    }
                    ?>