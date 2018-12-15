<?php

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

if ($database->has("groups", ['groupid' => $VARS['gid']])) {
    $groupid = $VARS['gid'];
} else {
    sendJsonResp($Strings->get("group does not exist", false), "ERROR");
}

if (!empty($VARS["get"]) && $VARS['get'] == "username") {
    $users = $database->select('assigned_groups', ['[>]accounts' => ['uid' => 'uid']], 'username', ['groupid' => $groupid, "ORDER" => "username"]);
} else if (!empty($VARS["get"]) && $VARS['get'] == "detail") {
    $users = $database->select('assigned_groups', ['[>]accounts' => ['uid' => 'uid']], ['username', 'realname (name)', 'accounts.uid', 'pin'], ['groupid' => $groupid, "ORDER" => "realname"]);
    for ($i = 0; $i < count($users); $i++) {
        if (is_null($users[$i]['pin']) || $users[$i]['pin'] == "") {
            $users[$i]['pin'] = false;
        } else {
            $users[$i]['pin'] = true;
        }
    }
} else {
    $users = $database->select('assigned_groups', 'uid', ['groupid' => $groupid]);
}
exitWithJson(["status" => "OK", "users" => $users]);
