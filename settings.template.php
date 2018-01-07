<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */


// Whether to show debugging data in output.
// DO NOT SET TO TRUE IN PRODUCTION!!!
define("DEBUG", false);

// Database connection settings
// See http://medoo.in/api/new for info
define("DB_TYPE", "mysql");
define("DB_NAME", "accounthub");
define("DB_SERVER", "localhost");
define("DB_USER", "accounthub");
define("DB_PASS", "");
define("DB_CHARSET", "utf8");

define("LDAP_ENABLED", FALSE);

// See https://github.com/ldaptools/ldaptools/blob/master/docs/en/reference/Main-Configuration.md
// for info on the LDAP config
/*
 * Begin LDAP Configuration
 */
use LdapTools\Configuration;
use LdapTools\DomainConfiguration;

$ldap_config = new Configuration();
$ldap_config_domain = (new DomainConfiguration('example'))
        ->setDomainName("example.com")
        ->setServers(['192.168.25.131'])
        ->setLazyBind(TRUE)
        ->setUsername("admin-account")
        ->setPassword("password")
        ->setUseTls(TRUE);
$ldap_config->addDomain($ldap_config_domain);
/*
 * End LDAP Configuration
 */

define("SITE_TITLE", "AccountHub");

// Which pages to show the app icon on:
// index, app, both, none
define("SHOW_ICON", "both");
// Where to put the icon: top or menu
// Overridden to 'menu' if MENU_BAR_STYLE is 'fixed'.
define("ICON_POSITION", "menu");
// App menu bar style: fixed or static
define("MENU_BAR_STYLE", "fixed");

// Used to identify the system in OTP and other places
define("SYSTEM_NAME", "Netsyms SSO Demo");

// For supported values, see http://php.net/manual/en/timezones.php
define("TIMEZONE", "America/Denver");

// Allow or prevent users from logging in via the mobile app.
define("MOBILE_ENABLED", TRUE);

// Base URL for site links.
define('URL', 'http://localhost/accounthub');

// Use reCAPTCHA on login screen
// https://www.google.com/recaptcha/
define("RECAPTCHA_ENABLED", FALSE);
define('RECAPTCHA_SITE_KEY', '');
define('RECAPTCHA_SECRET_KEY', '');

// API URL and index URL for TaskFloor
define('TASKFLOOR_API', 'http://localhost/taskfloor/api.php');
define('TASKFLOOR_HOME', '/taskfloor/app.php');

define('INVENTORY_HOME', '/inventory/app.php');

define('QWIKCLOCK_API', 'http://localhost/qwikclock/api.php');
define('QWIKCLOCK_HOME', '/qwikclock/app.php');

// See lang folder for language options
define('LANGUAGE', "en_us");

// List of available applications, icons, and other info.
// Used in the mobile app and in the "dock" in AccountHub.
define('EXTERNAL_APPS', [
    "accounthub" => [
        "url" => "http://localhost/accounthub",
        "mobileapi" => "/mobile/index.php",
        "icon" => "/static/img/logo.svg",
        "title" => SITE_TITLE
    ],
    "binstack" => [
        "url" => "http://localhost/inventory",
        "mobileapi" => "/mobile/index.php",
        "icon" => "/static/img/logo.svg",
        "title" => "BinStack"
    ],
    "taskfloor" => [
        "url" => "http://localhost/taskfloor",
        "mobileapi" => "/mobile/index.php",
        "icon" => "/static/img/logo.svg",
        "title" => "TaskFloor",
        "station_features" => [
            "taskfloor_viewtasks",
            "taskfloor_viewmessages"
        ]
    ],
    "qwikclock" => [
        "url" => "http://localhost/qwikclock",
        "mobileapi" => "/mobile/index.php",
        "icon" => "/static/img/logo.svg",
        "title" => "QwikClock",
        "station_features" => [
            "qwikclock_punchinout",
            "qwikclock_myshifts",
            "qwikclock_jobs"
        ]
    ],
    "managepanel" => [
        "url" => "http://localhost/managepanel",
        "mobileapi" => "/mobile/index.php",
        "icon" => "/static/img/logo.svg",
        "title" => "ManagePanel"
    ]
]);

// Email settings for receiving admin alerts.
define("USE_SMTP", TRUE); // if FALSE, will use PHP's mail() instead
define("ADMIN_EMAIL", "");
define("FROM_EMAIL", "alert-noreply@apps.biz.netsyms.com");
define("SMTP_HOST", "");
define("SMTP_AUTH", true);
define("SMTP_PORT", 587);
define("SMTP_SECURE", 'tls');
define("SMTP_USER", "");
define("SMTP_PASS", "");
define("SMTP_ALLOW_INVALID_CERTIFICATE", TRUE);

// Minimum length for new passwords
// The system checks new passwords against the 500 worst passwords and rejects
// any matches.
// If you want to have additional password requirements, go edit action.php.
// However, all that does is encourage people to use the infamous
// "post-it password manager".  See also https://xkcd.com/936/ and
// http://stackoverflow.com/a/34166252 for reasons why forcing passwords
// like CaPs45$% is not actually a great idea.
// Encourage users to use 2-factor auth whenever possible.
define("MIN_PASSWORD_LENGTH", 8);

// Maximum number of rows to get in a query.
define("QUERY_LIMIT", 1000);



define("FOOTER_TEXT", "");
define("COPYRIGHT_NAME", "Netsyms Technologies");
//////////////////////////////////////////////////////////////