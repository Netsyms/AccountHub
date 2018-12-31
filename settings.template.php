<?php

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

// Settings for the app.
// Copy to settings.php and customize.

$SETTINGS = [
    // Whether to output debugging info like PHP notices, warnings,
    // and stacktraces.
    // Turning this on in production is a security risk and can sometimes break
    // things, such as JSON output where extra content is not expected.
    "debug" => false,
    // Database connection settings
    // See http://medoo.in/api/new for info
    "database" => [
        "type" => "mysql",
        "name" => "accounthub",
        "server" => "localhost",
        "user" => "accounthub",
        "password" => "",
        "charset" => "utf8"
    ],
    // Name of the app.
    "site_title" => "AccountHub",
    // Used to identify the system in OTP and other places
    "system_name" => "Netsyms AccountHub",
    // Allow login from the Netsyms mobile app
    "mobile_enabled" => true,
    // Allow users to signup for new accounts
    "signups_enabled" => false,
    // For supported values, see http://php.net/manual/en/timezones.php
    "timezone" => "America/Denver",
    // List of external apps connected to this system.
    // This list is used for generating the dashboard cards and in the
    // mobile app.
    "apps" => [
        "accounthub" => [
            "url" => "/accounthub",
            "mobileapi" => "/mobile/index.php",
            "icon" => "/static/img/logo.svg",
            "title" => "AccountHub"
        ],
        "qwikclock" => [
            "url" => "/qwikclock",
            "mobileapi" => "/mobile/index.php",
            "icon" => "/static/img/logo.svg",
            "title" => "QwikClock",
            "station_features" => [
                "qwikclock_punchinout",
                "qwikclock_myshifts",
                "qwikclock_jobs"
            ],
            "card" => [
                "color" => "blue",
                "string" => "Punch in and check work schedule"
            ]
        ],
        "binstack" => [
            "url" => "/binstack",
            "mobileapi" => "/mobile/index.php",
            "icon" => "/static/img/logo.svg",
            "title" => "BinStack",
            "card" => [
                "color" => "green",
                "string" => "Manage physical items"
            ]
        ],
        "newspen" => [
            "url" => "/newspen",
            "mobileapi" => "/mobile/index.php",
            "icon" => "/static/img/logo.svg",
            "title" => "NewsPen",
            "card" => [
                "color" => "purple",
                "string" => "Create and publish e-newsletters"
            ]
        ],
        "managepanel" => [
            "url" => "/managepanel",
            "mobileapi" => "/mobile/index.php",
            "icon" => "/static/img/logo.svg",
            "title" => "ManagePanel",
            "card" => [
                "color" => "brown",
                "string" => "Manage users, permissions, and security"
            ]
        ],
        "nickelbox" => [
            "url" => "/nickelbox",
            "mobileapi" => "/mobile/index.php",
            "icon" => "/static/img/logo.svg",
            "title" => "NickelBox",
            "card" => [
                "color" => "light-green",
                "text" => "dark",
                "string" => "Checkout customers and manage online orders"
            ]
        ],
        "sitewriter" => [
            "url" => "/sitewriter",
            "mobileapi" => "/mobile/index.php",
            "icon" => "/static/img/logo.svg",
            "title" => "SiteWriter",
            "card" => [
                "color" => "light-blue",
                "string" => "Build websites and manage contact form messages"
            ]
        ],
        "taskfloor" => [
            "url" => "/taskfloor",
            "mobileapi" => "/mobile/index.php",
            "icon" => "/static/img/logo.svg",
            "title" => "TaskFloor",
            "station_features" => [
                "taskfloor_viewtasks",
                "taskfloor_viewmessages"
            ],
            "card" => [
                "color" => "blue-grey",
                "string" => "Track jobs and assigned tasks"
            ]
        ]
    ],
    // Settings for sending emails.
    "email" => [
        // If false, will use PHP mail() instead of a server
        "use_smtp" => true,
        // Admin email for alerts
        "admin_email" => "",
        "from" => "alert-noreply@example.com",
        "host" => "",
        "auth" => true,
        "port" => 587,
        "secure" => "tls",
        "user" => "",
        "password" => "",
        "allow_invalid_certificate" => true
    ],
    "min_password_length" => 8,
    // Show or hide the Station PIN setup option.
    "station_kiosk" => true,
    // Used for notification timestamp display.
    "datetime_format" => "M j, g:i a",
    "time_format" => "g:i",
    // Use Captcheck on login screen to slow down bots
    // https://captcheck.netsyms.com
    "captcha" => [
        "enabled" => false,
        "server" => "https://captcheck.netsyms.com"
    ],
    // Language to use for localization. See langs folder to add a language.
    "language" => "en",
    // Shown in the footer of all the pages.
    "footer_text" => "",
    // Also shown in the footer, but with "Copyright <current_year>" in front.
    "copyright" => "Netsyms Technologies",
    // Base URL for building links relative to the location of the app.
    "url" => "/accounthub/"
];
