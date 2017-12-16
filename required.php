<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

/**
 * This file contains global settings and utility functions.
 */
ob_start(); // allow sending headers after content
// Unicode, solves almost all stupid encoding problems
header('Content-Type: text/html; charset=utf-8');

// l33t $ecurity h4x
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('X-Powered-By: PHP'); // no versions makes it harder to find vulns
header('X-Frame-Options: "DENY"');
header('Referrer-Policy: "no-referrer, strict-origin-when-cross-origin"');
$SECURE_NONCE = base64_encode(random_bytes(8));


$session_length = 60 * 60; // 1 hour
session_set_cookie_params($session_length, "/", null, false, false);

session_start(); // stick some cookies in it
//// renew session cookie
setcookie(session_name(), session_id(), time() + $session_length);

if ($_SESSION['mobile'] === TRUE) {
    header("Content-Security-Policy: "
            . "default-src 'self';"
            . "object-src 'none'; "
            . "img-src * data:; "
            . "media-src 'self'; "
            . "frame-src 'none'; "
            . "font-src 'self'; "
            . "connect-src *; "
            . "style-src 'self' 'unsafe-inline'; "
            . "script-src 'self' 'unsafe-inline'");
} else {
    header("Content-Security-Policy: "
            . "default-src 'self';"
            . "object-src 'none'; "
            . "img-src * data:; "
            . "media-src 'self'; "
            . "frame-src 'none'; "
            . "font-src 'self'; "
            . "connect-src *; "
            . "style-src 'self' 'nonce-$SECURE_NONCE'; "
            . "script-src 'self' 'nonce-$SECURE_NONCE'");
}
//
// Composer
require __DIR__ . '/vendor/autoload.php';

// Settings file
require __DIR__ . '/settings.php';
// List of alert messages
require __DIR__ . '/lang/messages.php';
// text strings (i18n)
require __DIR__ . '/lang/' . LANGUAGE . ".php";

function sendError($error) {
    global $SECURE_NONCE;
    die("<!DOCTYPE html>"
            . "<meta charset=\"UTF-8\">"
            . "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">"
            . "<title>Error</title>"
            . "<style nonce=\"" . $SECURE_NONCE . "\">"
            . "h1 {color: red; font-family: sans-serif; font-size: 20px; margin-bottom: 0px;} "
            . "h2 {font-family: sans-serif; font-size: 16px;} "
            . "p {font-family: monospace; font-size: 14px; width: 100%; wrap-style: break-word;} "
            . "i {font-size: 12px;}"
            . "</style>"
            . "<h1>A fatal application error has occurred.</h1>"
            . "<i>(This isn't your fault.)</i>"
            . "<h2>Details:</h2>"
            . "<p>". htmlspecialchars($error) . "</p>");
}

date_default_timezone_set(TIMEZONE);

// Database settings
// Also inits database and stuff
use Medoo\Medoo;

$database;
try {
    $database = new Medoo([
        'database_type' => DB_TYPE,
        'database_name' => DB_NAME,
        'server' => DB_SERVER,
        'username' => DB_USER,
        'password' => DB_PASS,
        'charset' => DB_CHARSET
    ]);
} catch (Exception $ex) {
    //header('HTTP/1.1 500 Internal Server Error');
    sendError("Database error.  Try again later.  $ex");
}


if (!DEBUG) {
    error_reporting(0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 'On');
}


$VARS;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $VARS = $_POST;
    define("GET", false);
} else {
    $VARS = $_GET;
    define("GET", true);
}

/**
 * Checks if a string or whatever is empty.
 * @param $str The thingy to check
 * @return boolean True if it's empty or whatever.
 */
function is_empty($str) {
    return (is_null($str) || !isset($str) || $str == '');
}

/**
 * I18N string getter.  If the key doesn't exist, outputs the key itself.
 * @param string $key I18N string key
 * @param boolean $echo whether to echo the result or return it (default echo)
 */
function lang($key, $echo = true) {
    if (array_key_exists($key, $GLOBALS['STRINGS'])) {
        $str = $GLOBALS['STRINGS'][$key];
    } else {
        trigger_error("Language key \"$key\" does not exist in " . LANGUAGE, E_USER_WARNING);
        $str = $key;
    }

    if ($echo) {
        echo $str;
    } else {
        return $str;
    }
}

/**
 * I18N string getter (with builder).    If the key doesn't exist, outputs the key itself.
 * @param string $key I18N string key
 * @param array $replace key-value array of replacements.
 * If the string value is "hello {abc}" and you give ["abc" => "123"], the
 * result will be "hello 123".
 * @param boolean $echo whether to echo the result or return it (default echo)
 */
function lang2($key, $replace, $echo = true) {
    if (array_key_exists($key, $GLOBALS['STRINGS'])) {
        $str = $GLOBALS['STRINGS'][$key];
    } else {
        trigger_error("Language key \"$key\" does not exist in " . LANGUAGE, E_USER_WARNING);
        $str = $key;
    }

    foreach ($replace as $find => $repl) {
        $str = str_replace("{" . $find . "}", $repl, $str);
    }

    if ($echo) {
        echo $str;
    } else {
        return $str;
    }
}

/**
 * Add strings to the i18n global array.
 * @param array $strings ['key' => 'value']
 */
function addLangStrings($strings) {
    $GLOBALS['STRINGS'] = array_merge($GLOBALS['STRINGS'], $strings);
}

/**
 * Add strings to the i18n global array.  Accepts an array of language code 
 * keys, with the values a key-value array of strings.
 * @param array $strings ['en_us' => ['key' => 'value']]
 */
function addMultiLangStrings($strings) {
    if (!is_empty($strings[LANGUAGE])) {
        $GLOBALS['STRINGS'] = array_merge($GLOBALS['STRINGS'], $strings[LANGUAGE]);
    }
}

/**
 * Checks if an email address is valid.
 * @param string $email Email to check
 * @return boolean True if email passes validation, else false.
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Hashes the given plaintext password
 * @param String $password
 * @return String the hash, using bcrypt
 */
function encryptPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Securely verify a password and its hash
 * @param String $password
 * @param String $hash the hash to compare to
 * @return boolean True if password OK, else false
 */
function comparePassword($password, $hash) {
    return password_verify($password, $hash);
}

function dieifnotloggedin() {
    if ($_SESSION['loggedin'] != true) {
        sendError("Session expired.  Please log out and log in again.");
    }
}

/**
 * Check if the previous database action had a problem.
 * @param array $specials int=>string array with special response messages for SQL errors
 */
function checkDBError($specials = []) {
    global $database;
    $errors = $database->error();
    if (!is_null($errors[1])) {
        foreach ($specials as $code => $text) {
            if ($errors[1] == $code) {
                sendError($text);
            }
        }
        sendError("A database error occurred:<br /><code>" . $errors[2] . "</code>");
    }
}

/*
 * http://stackoverflow.com/a/20075147
 */
if (!function_exists('base_url')) {

    function base_url($atRoot = FALSE, $atCore = FALSE, $parse = FALSE) {
        if (isset($_SERVER['HTTP_HOST'])) {
            $http = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
            $hostname = $_SERVER['HTTP_HOST'];
            $dir = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);

            $core = preg_split('@/@', str_replace($_SERVER['DOCUMENT_ROOT'], '', realpath(dirname(__FILE__))), NULL, PREG_SPLIT_NO_EMPTY);
            $core = $core[0];

            $tmplt = $atRoot ? ($atCore ? "%s://%s/%s/" : "%s://%s/") : ($atCore ? "%s://%s/%s/" : "%s://%s%s");
            $end = $atRoot ? ($atCore ? $core : $hostname) : ($atCore ? $core : $dir);
            $base_url = sprintf($tmplt, $http, $hostname, $end);
        } else
            $base_url = 'http://localhost/';

        if ($parse) {
            $base_url = parse_url($base_url);
            if (isset($base_url['path']))
                if ($base_url['path'] == '/')
                    $base_url['path'] = '';
        }

        return $base_url;
    }

}

function redirectToPageId($id, $args, $dontdie) {
    header('Location: ' . URL . '?id=' . $id . $args);
    if (is_null($dontdie)) {
        die("Please go to " . URL . '?id=' . $id . $args);
    }
}

function redirectIfNotLoggedIn() {
    if ($_SESSION['loggedin'] !== TRUE) {
        header('Location: ' . URL . '/login.php');
        die();
    }
}

/**
 * Check if a given ipv4 address is in a given cidr
 * @param  string $ip    IP to check in IPV4 format eg. 127.0.0.1
 * @param  string $range IP/CIDR netmask eg. 127.0.0.0/24, also 127.0.0.1 is accepted and /32 assumed
 * @return boolean true if the ip is in this range / false if not.
 * @author Thorsten Ott <https://gist.github.com/tott/7684443>
 */
function ip4_in_cidr($ip, $cidr) {
    if (strpos($cidr, '/') == false) {
        $cidr .= '/32';
    }
    // $range is in IP/CIDR format eg 127.0.0.1/24
    list( $cidr, $netmask ) = explode('/', $cidr, 2);
    $range_decimal = ip2long($cidr);
    $ip_decimal = ip2long($ip);
    $wildcard_decimal = pow(2, ( 32 - $netmask)) - 1;
    $netmask_decimal = ~ $wildcard_decimal;
    return ( ( $ip_decimal & $netmask_decimal ) == ( $range_decimal & $netmask_decimal ) );
}

/**
 * Check if a given ipv6 address is in a given cidr
 * @param string $ip IP to check in IPV6 format
 * @param string $cidr CIDR netmask
 * @return boolean true if the IP is in this range, false otherwise.
 * @author MW. <https://stackoverflow.com/a/7952169>
 */
function ip6_in_cidr($ip, $cidr) {
    $address = inet_pton($ip);
    $subnetAddress = inet_pton(explode("/", $cidr)[0]);
    $subnetMask = explode("/", $cidr)[1];

    $addr = str_repeat("f", $subnetMask / 4);
    switch ($subnetMask % 4) {
        case 0:
            break;
        case 1:
            $addr .= "8";
            break;
        case 2:
            $addr .= "c";
            break;
        case 3:
            $addr .= "e";
            break;
    }
    $addr = str_pad($addr, 32, '0');
    $addr = pack("H*", $addr);

    $binMask = $addr;
    return ($address & $binMask) == $subnetAddress;
}

/**
 * Check if the REMOTE_ADDR is on Cloudflare's network.
 * @return boolean true if it is, otherwise false
 */
function validateCloudflare() {
    if (filter_var($_SERVER["REMOTE_ADDR"], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        // Using IPv6
        $cloudflare_ips_v6 = [
            "2400:cb00::/32",
            "2405:8100::/32",
            "2405:b500::/32",
            "2606:4700::/32",
            "2803:f800::/32",
            "2c0f:f248::/32",
            "2a06:98c0::/29"
        ];
        $valid = false;
        foreach ($cloudflare_ips_v6 as $cidr) {
            if (ip6_in_cidr($_SERVER["REMOTE_ADDR"], $cidr)) {
                $valid = true;
                break;
            }
        }
    } else {
        // Using IPv4
        $cloudflare_ips_v4 = [
            "103.21.244.0/22",
            "103.22.200.0/22",
            "103.31.4.0/22",
            "104.16.0.0/12",
            "108.162.192.0/18",
            "131.0.72.0/22",
            "141.101.64.0/18",
            "162.158.0.0/15",
            "172.64.0.0/13",
            "173.245.48.0/20",
            "188.114.96.0/20",
            "190.93.240.0/20",
            "197.234.240.0/22",
            "198.41.128.0/17"
        ];
        $valid = false;
        foreach ($cloudflare_ips_v4 as $cidr) {
            if (ip4_in_cidr($_SERVER["REMOTE_ADDR"], $cidr)) {
                $valid = true;
                break;
            }
        }
    }
    return $valid;
}

/**
 * Makes a good guess at the client's real IP address.
 *
 * @return string Client IP or `0.0.0.0` if we can't find anything
 */
function getClientIP() {
    // If CloudFlare is in the mix, we should use it.
    // Check if the request is actually from CloudFlare before trusting it.
    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
        if (validateCloudflare()) {
            return $_SERVER["HTTP_CF_CONNECTING_IP"];
        }
    }

    if (isset($_SERVER["REMOTE_ADDR"])) {
        return $_SERVER["REMOTE_ADDR"];
    }

    return "0.0.0.0"; // This will not happen unless we aren't a web server
}

/**
 * Check if the client's IP has been doing too many brute-force-friendly 
 * requests lately.
 * Kills the script with a "friendly" error and response code 429 
 * (Too Many Requests) if the last access time in the DB is too near.
 * 
 * Also updates the rate_limit table with the latest data and purges old rows. 
 * @global type $database
 */
function engageRateLimit() {
    global $database;
    $delay = date("Y-m-d H:i:s", strtotime("-2 seconds"));
    $database->delete('rate_limit', ["lastaction[<]" => $delay]);
    if ($database->has('rate_limit', ["AND" => ["ipaddr" => getClientIP()]])) {
        http_response_code(429);
        // JSONify it so API clients don't scream too loud
        die(json_encode(["status" => "ERROR", "msg" => "You're going too fast.  Slow down, mkay?"]));
    } else {
        // Add a record for the IP address
        $database->insert('rate_limit', ["ipaddr" => getClientIP(), "lastaction" => date("Y-m-d H:i:s")]);
    }
}
