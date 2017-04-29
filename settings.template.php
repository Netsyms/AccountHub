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

define("LDAP_ENABLED", TRUE);

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
        ->setUsername("readonly-bind")
        ->setPassword("password")
        ->setUseTls(TRUE);
$ldap_config->addDomain($ldap_config_domain);
/*
 * End LDAP Configuration
 */

define("SITE_TITLE", "Netsyms Business Apps :: Single Sign On");

// Used to identify the system in OTP and other places
define("SYSTEM_NAME", "Netsyms SSO Demo");

// For supported values, see http://php.net/manual/en/timezones.php
define("TIMEZONE", "America/Denver");

// Base URL for site links.
define('URL', 'http://localhost:8000/');

// Use reCAPTCHA on login screen
// https://www.google.com/recaptcha/
define("RECAPTCHA_ENABLED", FALSE);
define('RECAPTCHA_SITE_KEY', '');
define('RECAPTCHA_SECRET_KEY', '');

// See lang folder for language options
define('LANGUAGE', "en_us");

// Minimum length for new passwords
// The system checks new passwords against the 500 worst passwords and rejects
// any matches.
// If you want to have additional password requirements, go edit action.php.
// However, all that does is encourage people to use the infamous 
// "post-it password manager".  See also https://xkcd.com/936/ and
// http://stackoverflow.com/a/34166252/2534036 for reasons why forcing passwords
// like CaPs45$% is not actually a great idea.
// Encourage users to use 2-factor auth whenever possible.
define("MIN_PASSWORD_LENGTH", 8);

// Maximum number of rows to get in a query.
define("QUERY_LIMIT", 1000);



//////////////////////////////////////////////////////////////
//  /!\       Warning: Changing these values may       /!\  //
//  /!\  violate the terms of your license agreement!  /!\  //
//////////////////////////////////////////////////////////////
define("LICENSE_TEXT", "<b>Unlicensed Demo: For Trial Use Only</b>");
define("COPYRIGHT_NAME", "Netsyms Technologies");
//////////////////////////////////////////////////////////////