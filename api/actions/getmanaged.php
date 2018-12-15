<?php

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

if (!empty($VARS['uid'])) {
    $manager = new User($VARS['uid']);
} else if (!empty($VARS['username'])) {
    $manager = User::byUsername($VARS['username']);
}

if (!$manager->exists()) {
    exitWithJson(["status" => "ERROR", "msg" => $Strings->get("user does not exist", false)]);
}
if (!empty($VARS['get']) && $VARS['get'] == "username") {
    $managed = $database->select('managers', ['[>]accounts' => ['employeeid' => 'uid']], 'username', ['managerid' => $manager->getUID()]);
} else {
    $managed = $database->select('managers', 'employeeid', ['managerid' => $manager->getUID()]);
}
exitWithJson(["status" => "OK", "employees" => $managed]);
