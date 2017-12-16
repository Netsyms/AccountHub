<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

// List of pages and metadata
define("PAGES", [
    "home" => [
        "title" => "home",
        "navbar" => true,
        "icon" => "home"
    ],
    "security" => [
        "title" => "account options",
        "navbar" => true,
        "icon" => "cogs"
    ],
    "404" => [
        "title" => "404 error",
        "navbar" => false
    ]
]);


// Which apps to load on a given page
define("APPS", [
    "home" => [
        "taskfloor_tasks",
        "qwikclock_inout",
        "taskfloor_messages",
        "inventory_link",
        "account_security"
    ],
    "security" => [
        "sync_mobile",
        "change_password",
        "setup_2fa"
    ],
    "404" => [
        "404_error"
    ]
]);
