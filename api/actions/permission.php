<?php

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

$perm = $VARS['code'];
if (!empty($VARS['uid'])) {
    $user = new User($VARS['uid']);
} else if (!empty($VARS['username'])) {
    $user = User::byUsername($VARS['username']);
}

if (!$user->exists()) {
    exitWithJson(["status" => "ERROR", "msg" => $Strings->get("user does not exist", false)]);
}
exitWithJson(["status" => "OK", "has_permission" => $user->hasPermission($perm)]);
