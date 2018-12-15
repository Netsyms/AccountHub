<?php

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

if (strlen($VARS['search']) < 2) {
    exitWithJson(["status" => "OK", "result" => []]);
}
$data = $database->select('groups', ['groupid (id)', 'groupname (name)'], ['groupname[~]' => $VARS['search'], "LIMIT" => 10]);
exitWithJson(["status" => "OK", "result" => $data]);
