<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

// List of pages and metadata
define("PAGES", [
    "home" => [
        "title" => "Home",
        "navbar" => true,
        "icon" => "fas fa-home",
        "styles" => [
            "static/css/dock.css"
        ]
    ],
    "security" => [
        "title" => "account security",
        "navbar" => true,
        "icon" => "fas fa-lock",
        "styles" => [
            "static/css/qrcode.css"
        ]
    ],
    "sync" => [
        "title" => "sync",
        "navbar" => true,
        "icon" => "fas fa-sync"
    ],
    "404" => [
        "title" => "404 error"
    ]
]);
