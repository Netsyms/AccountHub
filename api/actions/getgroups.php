<?php

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

$groups = $database->select('groups', ['groupid (id)', 'groupname (name)']);
exitWithJson(["status" => "OK", "groups" => $groups]);
