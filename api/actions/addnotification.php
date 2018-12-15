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
    $timestamp = "";
    if (!empty($VARS['timestamp'])) {
        $timestamp = date("Y-m-d H:i:s", strtotime($VARS['timestamp']));
    }
    $url = "";
    if (!empty($VARS['url'])) {
        $url = $VARS['url'];
    }
    $nid = Notifications::add($user, $VARS['title'], $VARS['content'], $timestamp, $url, isset($VARS['sensitive']));

    exitWithJson(["status" => "OK", "id" => $nid]);
} catch (Exception $ex) {
    sendJsonResp($ex->getMessage(), "ERROR");
}