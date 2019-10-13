<?php

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

$code = strtoupper(substr(md5(mt_rand() . uniqid("", true)), 0, 20));
$desc = htmlspecialchars($VARS['desc']);
$chunk_code = str_replace(" ", "-", trim(chunk_split($code, 5, ' ')));
$database->insert('apppasswords', ['uid' => User::byUsername($VARS['username'])->getUID(), 'hash' => password_hash($chunk_code, PASSWORD_DEFAULT), 'description' => $desc]);

sendJsonResp("", "OK", ["pass" => $chunk_code]);
