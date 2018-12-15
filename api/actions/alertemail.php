<?php

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

engageRateLimit();
$appname = "???";
if (!empty($VARS['appname'])) {
    $appname = $VARS['appname'];
}
$result = User::byUsername($VARS['username'])->sendAlertEmail($appname);
if ($result === TRUE) {
    sendJsonResp();
}
sendJsonResp($result, "ERROR");
