<?php

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

// Make sure the username and key are actually legit
$user_key_valid = $database->has('mobile_codes', ['[>]accounts' => ['uid' => 'uid']], ["AND" => ['mobile_codes.code' => $VARS['key'], 'accounts.username' => $VARS['username']]]);
if ($user_key_valid !== TRUE) {
    engageRateLimit();
    http_response_code(401);
    insertAuthLog(21, null, "Username: " . $VARS['username'] . ", Key: " . $VARS['key']);
    die(json_encode(["status" => "ERROR", "msg" => "Invalid username and/or access key."]));
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
        if (get_account_status($VARS['username']) != "NORMAL") {
            insertAuthLog(20, null, "Username: " . $VARS['username'] . ", Key: " . $VARS['key']);
            exit(json_encode(["status" => "ERROR", "msg" => lang("login failed try on web", false)]));
        }
        if (authenticate_user($VARS['username'], $VARS['password'], $autherror)) {
            $uid = $database->get("accounts", "uid", ["username" => $VARS['username']]);
            insertAuthLog(19, $uid, "Key: " . $VARS['key']);
            exit(json_encode(["status" => "OK", "uid" => $uid]));
        } else {
            if (!is_empty($autherror)) {
                insertAuthLog(20, null, "Username: " . $VARS['username'] . ", Key: " . $VARS['key']);
                exit(json_encode(["status" => "ERROR", "msg" => $autherror]));
            } else {
                insertAuthLog(20, null, "Username: " . $VARS['username'] . ", Key: " . $VARS['key']);
                exit(json_encode(["status" => "ERROR", "msg" => lang("login incorrect", false)]));
            }
        }
    case "user_info":
        engageRateLimit();
        if (get_account_status($VARS['username']) != "NORMAL") {
            insertAuthLog(20, null, "Username: " . $VARS['username'] . ", Key: " . $VARS['key']);
            exit(json_encode(["status" => "ERROR", "msg" => lang("login failed try on web", false)]));
        }
        if (authenticate_user($VARS['username'], $VARS['password'], $autherror)) {
            $userinfo = $database->get("accounts", ["uid", "username", "realname", "email"], ["username" => $VARS['username']]);
            insertAuthLog(19, $userinfo['uid'], "Key: " . $VARS['key']);
            exit(json_encode(["status" => "OK", "info" => $userinfo]));
        } else {
            if (!is_empty($autherror)) {
                insertAuthLog(20, null, "Username: " . $VARS['username'] . ", Key: " . $VARS['key']);
                exit(json_encode(["status" => "ERROR", "msg" => $autherror]));
            } else {
                insertAuthLog(20, null, "Username: " . $VARS['username'] . ", Key: " . $VARS['key']);
                exit(json_encode(["status" => "ERROR", "msg" => lang("login incorrect", false)]));
            }
        }
    case "start_session":
        // Do a web login.
        engageRateLimit();
        if (user_exists($VARS['username'])) {
            if (get_account_status($VARS['username']) == "NORMAL") {
                if (authenticate_user($VARS['username'], $VARS['password'], $autherror)) {
                    doLoginUser($VARS['username'], $VARS['password']);
                    exit(json_encode(["status" => "OK"]));
                }
            }
        }
        exit(json_encode(["status" => "ERROR", "msg" => lang("login incorrect", false)]));
    case "listapps":
        exit(json_encode(["status" => "OK", "apps" => EXTERNAL_APPS]));
    default:
        http_response_code(404);
        die(json_encode(["status" => "ERROR", "msg" => "The requested action is not available."]));
}