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
        "title" => "account security",
        "navbar" => true,
        "icon" => "lock"
    ],
    "sync" => [
        "title" => "sync",
        "navbar" => true,
        "icon" => "mobile"
    ],
    "404" => [
        "title" => "404 error",
        "navbar" => false
    ]
]);


// Which apps to load on a given page
define("APPS", [
    "home" => [],
    "security" => [
        "change_password",
        "change_pin",
        "setup_2fa",
    ],
    "sync" => [
        "sync_mobile",
    ],
    "404" => [
        "404_error"
    ]
]);
