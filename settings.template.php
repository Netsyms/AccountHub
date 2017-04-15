<?php

// Whether to show debugging data in output.
// DO NOT SET TO TRUE IN PRODUCTION!!!
define("DEBUG", false);

// Database connection settings
// See http://medoo.in/api/new for info
define("DB_TYPE", "mysql");
define("DB_NAME", "sso");
define("DB_SERVER", "localhost");
define("DB_USER", "sso");
define("DB_PASS", "");
define("DB_CHARSET", "utf8");

define("LDAP_SERVER", "example.com");
define("LDAP_BASEDN", "ou=users,dc=example,dc=com");

define("SITE_TITLE", "Netsyms Business Apps :: Single Sign On");

// Used to identify the system in OTP and other places
define("SYSTEM_NAME", "Netsyms SSO Demo");

define("COPYRIGHT_NAME", "Netsyms Technologies");

// For supported values, see http://php.net/manual/en/timezones.php
define("TIMEZONE", "America/Denver");

// Base URL for site links.
define('URL', 'http://localhost:8000/');

// See lang folder for language options
define('LANGUAGE', "en_us");

// Maximum number of rows to get in a query.
define("QUERY_LIMIT", 1000);