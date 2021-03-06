<?php

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

engageRateLimit();
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
    switch ($user->getStatus()->getString()) {
        case "LOCKED_OR_DISABLED":
            Log::insert(LogType::API_LOGIN_FAILED, $uid, "Username: " . strtolower($VARS['username']) . ", Key: " . getCensoredKey());
            exitWithJson(["status" => "ERROR", "msg" => $Strings->get("account locked", false)]);
        case "TERMINATED":
            Log::insert(LogType::API_LOGIN_FAILED, $uid, "Username: " . strtolower($VARS['username']) . ", Key: " . getCensoredKey());
            exitWithJson(["status" => "ERROR", "msg" => $Strings->get("account terminated", false)]);
        case "CHANGE_PASSWORD":
            Log::insert(LogType::API_LOGIN_FAILED, $uid, "Username: " . strtolower($VARS['username']) . ", Key: " . getCensoredKey());
            exitWithJson(["status" => "ERROR", "msg" => $Strings->get("password expired", false)]);
        case "NORMAL":
            Log::insert(LogType::API_LOGIN_OK, $uid, "Username: " . strtolower($VARS['username']) . ", Key: " . getCensoredKey());
            exitWithJson(["status" => "OK"]);
        case "ALERT_ON_ACCESS":
            $user->sendAlertEmail();
            Log::insert(LogType::API_LOGIN_OK, $uid, "Username: " . strtolower($VARS['username']) . ", Key: " . getCensoredKey());
            exitWithJson(["status" => "OK", "alert" => true]);
        default:
            Log::insert(LogType::API_LOGIN_FAILED, $uid, "Username: " . strtolower($VARS['username']) . ", Key: " . getCensoredKey());
            exitWithJson(["status" => "ERROR", "msg" => $Strings->get("account state error", false)]);
    }
} else {
    Log::insert(LogType::API_LOGIN_FAILED, null, "Username: " . strtolower($VARS['username']) . ", Key: " . getCensoredKey());
    exitWithJson(["status" => "ERROR", "msg" => $Strings->get("login incorrect", false)]);
}