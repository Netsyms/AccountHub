<?php

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

if (!empty($VARS['uid'])) {
    $user = new User($VARS['uid']);
} else if (!empty($VARS['username'])) {
    $user = User::byUsername($VARS['username']);
}

sendJsonResp(null, "OK", ["exists" => $user->exists()]);
