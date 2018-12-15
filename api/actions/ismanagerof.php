<?php

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

if (!empty($VARS['uid']) && $VARS['uid'] == "1") {
    $manager = new User($VARS['manager']);
    $employee = new User($VARS['employee']);
} else {
    $manager = User::byUsername($VARS['manager']);
    $employee = User::byUsername($VARS['employee']);
}
if (!$manager->exists()) {
    exitWithJson(["status" => "ERROR", "msg" => $Strings->get("user does not exist", false), "user" => $VARS['manager']]);
}
if (!$employee->exists()) {
    exitWithJson(["status" => "ERROR", "msg" => $Strings->get("user does not exist", false), "user" => $VARS['employee']]);
}

if ($database->has('managers', ['AND' => ['managerid' => $manager->getUID(), 'employeeid' => $employee->getUID()]])) {
    exitWithJson(["status" => "OK", "managerof" => true]);
} else {
    exitWithJson(["status" => "OK", "managerof" => false]);
}