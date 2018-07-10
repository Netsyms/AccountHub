<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

dieifnotloggedin();

$newpin = $Strings->get("new pin", false);
$conpin = $Strings->get("confirm pin", false);
$change = $Strings->get("change pin", false);
$pinexp = $Strings->get("pin explanation", false);


$APPS["change_pin"]["title"] = $Strings->get("change pin", false);
$APPS["change_pin"]["icon"] = "th";
$APPS["change_pin"]["content"] = <<<CONTENTEND
<div class="alert alert-info"><i class="fa fa-info-circle"></i> $pinexp</div>
<form action="action.php" method="POST">
    <input type="password" class="form-control" name="newpin" placeholder="$newpin" maxlength="8" pattern="[0-9]*" inputmode="numeric" />
    <input type="password" class="form-control" name="conpin" placeholder="$conpin" maxlength="8" pattern="[0-9]*" inputmode="numeric" />
    <input type="hidden" name="action" value="chpin" />
    <input type="hidden" name="source" value="security" />
    <br />
    <button type="submit" class="btn btn-success btn-sm btn-block">$change</button>
</form>
CONTENTEND;
