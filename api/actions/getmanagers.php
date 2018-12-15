<?php

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

if (!empty($VARS['uid'])) {
    $emp = new User($VARS['uid']);
} else if (!empty($VARS['username'])) {
    $emp = User::byUsername($VARS['username']);
}

if (!$emp->exists()) {
    exitWithJson(["status" => "ERROR", "msg" => $Strings->get("user does not exist", false)]);
}
$managers = $database->select('managers', 'managerid', ['employeeid' => $emp->getUID()]);
exitWithJson(["status" => "OK", "managers" => $managers]);
