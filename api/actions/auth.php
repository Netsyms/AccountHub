<?php

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

$user = User::byUsername($VARS['username']);

$ok = false;
if (empty($VARS['apppass']) && ($user->checkPassword($VARS['password']) || $user->checkAppPassword($VARS['password']))) {
    $ok = true;
} else {
    if ((!$user->has2fa() && $user->checkPassword($VARS['password'])) || $user->checkAppPassword($VARS['password'])) {
        $ok = true;
    }
}
if ($ok) {
    Log::insert(LogType::API_AUTH_OK, null, "Username: " . strtolower($VARS['username']) . ", Key: " . getCensoredKey());
    sendJsonResp($Strings->get("login successful", false), "OK");
} else {
    Log::insert(LogType::API_AUTH_FAILED, $user->getUID(), "Username: " . strtolower($VARS['username']) . ", Key: " . getCensoredKey());
    if ($user->exists()) {
        switch ($user->getStatus()->get()) {
            case AccountStatus::LOCKED_OR_DISABLED:
                sendJsonResp($Strings->get("account locked", false), "ERROR");
            case AccountStatus::TERMINATED:
                sendJsonResp($Strings->get("account terminated", false), "ERROR");
            case AccountStatus::CHANGE_PASSWORD:
                sendJsonResp($Strings->get("password expired", false), "ERROR");
            case AccountStatus::NORMAL:
                break;
            default:
                sendJsonResp($Strings->get("account state error", false), "ERROR");
        }
    }
    sendJsonResp($Strings->get("login incorrect", false), "ERROR");
}