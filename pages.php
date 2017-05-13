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
        "sample_app"
    ],
    "security" => [
        "change_password",
        "setup_2fa"
    ],
    "404" => [
        "404_error"
    ]
]);
