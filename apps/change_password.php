<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

dieifnotloggedin();

$oldpass = $Strings->get("current password", false);
$newpass = $Strings->get("new password", false);
$conpass = $Strings->get("confirm password", false);
$change = $Strings->get("change password", false);

$APPS["change_password"]["title"] = $Strings->get("change password", false);
$APPS["change_password"]["icon"] = "key";
$APPS["change_password"]["content"] = <<<CONTENTEND
<form action="action.php" method="POST">
    <input type="password" class="form-control" name="oldpass" placeholder="$oldpass" />
    <input type="password" class="form-control" name="newpass" placeholder="$newpass" />
    <input type="password" class="form-control" name="conpass" placeholder="$conpass" />
    <input type="hidden" name="action" value="chpasswd" />
    <input type="hidden" name="source" value="security" />
    <br />
    <button type="submit" class="btn btn-success btn-sm btn-block">$change</button>
</form>
CONTENTEND;
