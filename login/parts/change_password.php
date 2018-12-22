<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

$_SESSION['check'] = "change_password";
$username = (new User($_SESSION['login_uid']))->getUsername();
?>

<form action="" method="POST">
    <div>
        <?php $Strings->get("password expired"); ?>
    </div>
    <div class="form-group">
        <label for="oldpassword"><?php $Strings->build("Current password for {user}", ["user" => htmlentities($username)]); ?></label>
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
            </div>
            <input type="password" class="form-control" id="oldpassword" name="oldpassword" placeholder="" required autofocus>
        </div>
    </div>

    <div class="form-group">
        <label for="newpassword"><?php $Strings->get("New password"); ?></label>
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
            </div>
            <input type="password" class="form-control" id="newpassword" name="newpassword" placeholder="" required>
        </div>
    </div>

    <div class="form-group">
        <label for="newpassword2"><?php $Strings->get("New password (again)"); ?></label>
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
            </div>
            <input type="password" class="form-control" id="newpassword2" name="newpassword2" placeholder="" required>
        </div>
    </div>

    <div class="d-flex">
        <button type="submit" class="btn btn-primary ml-auto">
            <i class="fas fa-chevron-right"></i> <?php $Strings->get("continue"); ?>
        </button>
    </div>
</form>