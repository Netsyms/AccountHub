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

try {
    Notifications::delete($user, $VARS['id']);
    sendJsonResp();
} catch (Exception $ex) {
    sendJsonResp($ex->getMessage(), "ERROR");
}