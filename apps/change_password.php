<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

dieifnotloggedin();

$oldpass = lang("current password", false);
$newpass = lang("new password", false);
$conpass = lang("confirm password", false);
$change = lang("change password", false);

$APPS["change_password"]["title"] = lang("change password", false);
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
