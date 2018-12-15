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
}
if ($user->exists()) {
    $data = $database->get("accounts", ["uid", "username", "realname (name)", "email", "phone" => ["phone1 (1)", "phone2 (2)"], 'pin'], ["uid" => $user->getUID()]);
    $data['pin'] = (is_null($data['pin']) || $data['pin'] == "" ? false : true);
    sendJsonResp(null, "OK", ["data" => $data]);
} else {
    sendJsonResp($Strings->get("login incorrect", false), "ERROR");
}