<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

/*
 * Mobile app API
 */

require __DIR__ . "/../required.php";

require __DIR__ . "/../lib/login.php";

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Allow ping check without authentication
if ($VARS['action'] == "ping") {
    exit(json_encode(["status" => "OK"]));
}

if (MOBILE_ENABLED !== TRUE) {
    exit(json_encode(["status" => "ERROR", "msg" => lang("mobile login disabled", false)]));
}

// Make sure we have a username and access key
if (is_empty($VARS['username']) || is_empty($VARS['key'])) {
    http_response_code(401);
    die(json_encode(["status" => "ERROR", "msg" => "Missing username and/or access key."]));
}

$username = strtolower($VARS['username']);
$key = strtoupper($VARS['key']);

// Make sure the username and key are actually legit
$user_key_valid = $database->has('mobile_codes', ['[>]accounts' => ['uid' => 'uid']], ["AND" => ['mobile_codes.code' => $key, 'accounts.username' => $username]]);
if ($user_key_valid !== TRUE) {
    engageRateLimit();
    //http_response_code(401);
    insertAuthLog(21, null, "Username: " . $username . ", Key: " . $key);
    die(json_encode(["status" => "ERROR", "msg" => "Invalid username and/or access key."]));
}

// Obscure key
if (strlen($key) > 7) {
    for ($i = 3; $i < strlen($key) - 3; $i++) {
        $key[$i] = "*";
    }
}

// Process the action
switch ($VARS['action']) {
    case "check_key":
        // Check if the username/key combo is valid.
        // If we get this far, it is, so return success.
        exit(json_encode(["status" => "OK"]));
    case "check_password":
        // Check if the user-supplied password is valid.
        engageRateLimit();
        if (get_account_status($username) != "NORMAL") {
            insertAuthLog(20, null, "Username: " . $username . ", Key: " . $key);
            exit(json_encode(["status" => "ERROR", "msg" => lang("login failed try on web", false)]));
        }
        if (authenticate_user($username, $VARS['password'], $autherror)) {
            $uid = $database->get("accounts", "uid", ["username" => $username]);
            insertAuthLog(19, $uid, "Key: " . $key);
            exit(json_encode(["status" => "OK", "uid" => $uid]));
        } else {
            if (!is_empty($autherror)) {
                insertAuthLog(20, null, "Username: " . $username . ", Key: " . $key);
                exit(json_encode(["status" => "ERROR", "msg" => $autherror]));
            } else {
                insertAuthLog(20, null, "Username: " . $username . ", Key: " . $key);
                exit(json_encode(["status" => "ERROR", "msg" => lang("login incorrect", false)]));
            }
        }
    case "user_info":
        engageRateLimit();
        if (get_account_status($username) != "NORMAL") {
            insertAuthLog(20, null, "Username: " . $username . ", Key: " . $key);
            exit(json_encode(["status" => "ERROR", "msg" => lang("login failed try on web", false)]));
        }
        if (authenticate_user($username, $VARS['password'], $autherror)) {
            $userinfo = $database->get("accounts", ["uid", "username", "realname", "email"], ["username" => $username]);
            insertAuthLog(19, $userinfo['uid'], "Key: " . $key);
            exit(json_encode(["status" => "OK", "info" => $userinfo]));
        } else {
            if (!is_empty($autherror)) {
                insertAuthLog(20, null, "Username: " . $username . ", Key: " . $key);
                exit(json_encode(["status" => "ERROR", "msg" => $autherror]));
            } else {
                insertAuthLog(20, null, "Username: " . $username . ", Key: " . $key);
                exit(json_encode(["status" => "ERROR", "msg" => lang("login incorrect", false)]));
            }
        }
    case "start_session":
        // Do a web login.
        engageRateLimit();
        if (user_exists($username)) {
            if (get_account_status($username) == "NORMAL") {
                if (authenticate_user($username, $VARS['password'], $autherror)) {
                    doLoginUser($username, $VARS['password']);
                    $_SESSION['mobile'] = true;
                    exit(json_encode(["status" => "OK"]));
                }
            }
        }
        insertAuthLog(20, null, "Username: " . $username . ", Key: " . $key);
        exit(json_encode(["status" => "ERROR", "msg" => lang("login incorrect", false)]));
    case "listapps":
        $apps = EXTERNAL_APPS;
        // Format paths as absolute URLs
        foreach ($apps as $k => $v) {
            if (strpos($apps[$k]['url'], "http") === FALSE) {
                $apps[$k]['url'] = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . ($_SERVER['SERVER_PORT'] != 80 || $_SERVER['SERVER_PORT'] != 443 ? ":" . $_SERVER['SERVER_PORT'] : "") . $apps[$k]['url'];
            }
        }
        exit(json_encode(["status" => "OK", "apps" => $apps]));
    case "gencode":
        engageRateLimit();
        $uid = $database->get("accounts", "uid", ["username" => $username]);
        $code = "";
        do {
            $code = random_int(100000, 999999);
        } while ($database->has("onetimekeys", ["key" => $code]));
        
        $database->insert("onetimekeys", ["key" => $code, "uid" => $uid, "expires" => date("Y-m-d H:i:s", strtotime("+1 minute"))]);
        
        $database->delete("onetimekeys", ["expires[<]" => date("Y-m-d H:i:s")]); // cleanup
        exit(json_encode(["status" => "OK", "code" => $code]));
    default:
        http_response_code(404);
        die(json_encode(["status" => "ERROR", "msg" => "The requested action is not available."]));
}