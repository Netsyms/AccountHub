<?php

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

$database->delete("onetimekeys", ["expires[<]" => date("Y-m-d H:i:s")]); // cleanup
if ($database->has("onetimekeys", ["key" => $VARS['code'], "expires[>]" => date("Y-m-d H:i:s")])) {
    $user = $database->get("onetimekeys", ["[>]accounts" => ["uid" => "uid"]], ["username", "realname", "accounts.uid"], ["key" => $VARS['code']]);
    exitWithJson(["status" => "OK", "user" => $user]);
} else {
    sendJsonResp($Strings->get("no such code or code expired", false), "ERROR");
}