<?php

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

if (!empty($VARS['uid'])) {
    if ($database->has("accounts", ['uid' => $VARS['uid']])) {
        $empid = $VARS['uid'];
    } else {
        sendJsonResp($Strings->get("user does not exist", false), "ERROR");
    }
} else if (!empty($VARS['username'])) {
    if ($database->has("accounts", ['username' => strtolower($VARS['username'])])) {
        $empid = $database->select('accounts', 'uid', ['username' => strtolower($VARS['username'])]);
    } else {
        sendJsonResp($Strings->get("user does not exist", false), "ERROR");
    }
}
$groups = $database->select('assigned_groups', ["[>]groups" => ["groupid" => "groupid"]], ['groups.groupid (id)', 'groups.groupname (name)'], ['uid' => $empid]);
exitWithJson(["status" => "OK", "groups" => $groups]);
