<?php

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

if (empty($VARS['username']) || empty($VARS['code'])) {
    http_response_code(400);
    die("\"400 Bad Request\"");
}
$code = strtoupper($VARS['code']);
$user_key_valid = $database->has('mobile_codes', ['[>]accounts' => ['uid' => 'uid']], ["AND" => ['mobile_codes.code' => $code, 'accounts.username' => strtolower($VARS['username'])]]);
exitWithJson(["status" => "OK", "valid" => $user_key_valid]);
