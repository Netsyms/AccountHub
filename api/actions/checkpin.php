<?php

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

$pin = "";
if (!empty($VARS['username'])) {
    $user = User::byUsername($VARS['username']);
} else if (!empty($VARS['uid'])) {
    $user = new User($VARS['uid']);
}

if ($user->exists()) {
    $pin = $database->get("accounts", "pin", ["uid" => $user->getUID()]);
} else {
    sendJsonResp($Strings->get("login incorrect", false), "ERROR");
}
if (is_null($pin) || $pin == "") {
    exitWithJson(["status" => "ERROR", "pinvalid" => false, "nopinset" => true]);
}
exitWithJson(["status" => "OK", "pinvalid" => ($pin == $VARS['pin'])]);
