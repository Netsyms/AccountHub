<?php

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

require __DIR__ . "/required.php";

date_default_timezone_set('UTC');

use \FeedWriter\RSS1;
use \FeedWriter\RSS2;
use \FeedWriter\ATOM;

if (empty($_GET['key']) || empty($_GET['type'])) {
    http_response_code(400);
    die("400 Bad Request: please send a user key and a feed type");
}

if (!$database->has('userkeys', ['key' => $_GET['key']])) {
    http_response_code(403);
    die("403 Forbidden: provide valid key");
}

$uid = $database->get('userkeys', 'uid', ['key' => $_GET['key']]);
$user = new User($uid);
switch ($user->getStatus()->get()) {
    case AccountStatus::NORMAL:
    case AccountStatus::CHANGE_PASSWORD:
    case AccountStatus::ALERT_ON_ACCESS:
        break;
    default:
        http_response_code(403);
        die("403 Forbidden: user account not active");
}

$notifications = Notifications::get($user);

switch ($_GET['type']) {
    case "rss1":
        $feed = new RSS1();
        break;
    case "rss":
    case "rss2":
        $feed = new RSS2();
        break;
    case "atom":
        $feed = new ATOM();
        break;
    default:
        http_response_code(400);
        die("400 Bad Request: feed parameter must have a value of \"rss\", \"rss1\", \"rss2\" or \"atom\".");
}

$feed->setTitle($Strings->build("Notifications from server for user", ['server' => $SETTINGS['site_title'], 'user' => $user->getName()], false));

if (strpos($SETTINGS['url'], "http") === 0) {
    $url = $SETTINGS['url'];
} else {
    $url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . (($_SERVER['SERVER_PORT'] != 80 && $_SERVER['SERVER_PORT'] != 443) ? ":" . $_SERVER['SERVER_PORT'] : "") . $SETTINGS['url'];
}

$feed->setLink($url);

foreach ($notifications as $n) {
    $item = $feed->createNewItem();
    $item->setTitle($n['title']);
    if (empty($n['url'])) {
        $item->setLink($url);
    } else {
        $item->setLink($n['url']);
    }
    $item->setDate(strtotime($n['timestamp']));
    if ($n['sensitive']) {
        $content = $Strings->get("Sensitive content hidden", false);
    } else {
        $content = $n['content'];
    }
    if ($_GET['type'] == "atom") {
        $item->setContent($content);
    } else {
        $item->setDescription($content);
    }
    $feed->addItem($item);
}

$feed->printFeed();
