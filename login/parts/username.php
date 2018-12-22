<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

$_SESSION['check'] = "username";
?>

<form action="" method="POST">
    <div class="form-group">
        <label for="username"><?php $Strings->get("username"); ?></label>
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-user"></i></span>
            </div>
            <input type="text" class="form-control" id="username" name="username" aria-describedby="usernameHelp" placeholder="" required autofocus>
        </div>
        <small id="usernameHelp" class="form-text text-muted">Enter your username.</small>
    </div>

    <div class="d-flex">
        <button type="submit" class="btn btn-primary ml-auto">
            <i class="fas fa-chevron-right"></i> <?php $Strings->get("continue"); ?>
        </button>
    </div>
</form>