<?php

// List of pages and metadata
define("PAGES", [
    "home" => [
        "title" => "{DEFAULT}"
    ],
    "security" => [
        "title" => "security options"
    ],
    "404" => [
        "title" => "404 error"
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
