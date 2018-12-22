<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

$_SESSION['check'] = "password";
$username = (new User($_SESSION['login_uid']))->getUsername();
?>

<form action="" method="POST">
    <div class="form-group">
        <label for="password"><?php $Strings->build("Password for {user}", ["user" => htmlentities($username)]); ?></label>
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
            </div>
            <input type="password" class="form-control" id="password" name="password" aria-describedby="passwordHelp" placeholder="" required autofocus>
        </div>
        <small id="passwordHelp" class="form-text text-muted">Enter your password.</small>
    </div>

    <div class="d-flex">
        <button type="submit" class="btn btn-primary ml-auto">
            <i class="fas fa-chevron-right"></i> <?php $Strings->get("continue"); ?>
        </button>
    </div>
</form>