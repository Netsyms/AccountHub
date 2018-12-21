<?php

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

$apps = $SETTINGS['apps'];
// Format paths as absolute URLs
foreach ($apps as $k => $v) {
    if (strpos($apps[$k]['url'], "http") === FALSE) {
        $apps[$k]['url'] = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . ($_SERVER['SERVER_PORT'] != 80 || $_SERVER['SERVER_PORT'] != 443 ? ":" . $_SERVER['SERVER_PORT'] : "") . $apps[$k]['url'];
    }
}
exitWithJson(["status" => "OK", "apps" => $apps]);
