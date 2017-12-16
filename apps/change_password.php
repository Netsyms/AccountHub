<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

dieifnotloggedin();

$APPS["change_password"]["title"] = "Change Password";
$APPS["change_password"]["icon"] = "key";
$APPS["change_password"]["content"] = <<<CONTENTEND
<form action="action.php" method="POST">
    <input type="password" class="form-control" name="oldpass" placeholder="Current password" />
    <input type="password" class="form-control" name="newpass" placeholder="New password" />
    <input type="password" class="form-control" name="conpass" placeholder="New password (again)" />
    <input type="hidden" name="action" value="chpasswd" />
    <input type="hidden" name="source" value="security" />
    <br />
    <button type="submit" class="btn btn-success btn-sm btn-block">Change Password</button>
</form>
CONTENTEND;
