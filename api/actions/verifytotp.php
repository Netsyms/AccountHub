<?php

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

$user = User::byUsername($VARS['username']);
if ($user->check2fa($VARS['code'])) {
    sendJsonResp(null, "OK", ["valid" => true]);
} else {
    Log::insert(LogType::API_BAD_2FA, null, "Username: " . strtolower($VARS['username']) . ", Key: " . getCensoredKey());
    sendJsonResp($Strings->get("2fa incorrect", false), "ERROR", ["valid" => false]);
}