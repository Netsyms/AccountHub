<?php

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

if (!empty($VARS['username'])) {
    $user = User::byUsername($VARS['username']);
} else if (!empty($VARS['uid'])) {
    $user = new User($VARS['uid']);
} else {
    http_response_code(400);
    die("\"400 Bad Request\"");
}
if (empty($VARS['id'])) {
    exit(json_encode(["status" => "ERROR", "msg" => $Strings->get("invalid parameters", false)]));
}
try {
    Notifications::read($user, $VARS['id']);
    sendJsonResp();
} catch (Exception $ex) {
    sendJsonResp($ex->getMessage(), "ERROR");
}