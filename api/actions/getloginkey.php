<?php

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

$code = LoginKey::generate($VARS['appname']);

if (strpos($SETTINGS['url'], "http") === 0) {
    $url = $SETTINGS['url'] . "login/";
} else {
    $url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . (($_SERVER['SERVER_PORT'] != 80 && $_SERVER['SERVER_PORT'] != 443) ? ":" . $_SERVER['SERVER_PORT'] : "") . $SETTINGS['url'] . "login/";
}

exitWithJson(["status" => "OK", "code" => $code, "loginurl" => $url]);
