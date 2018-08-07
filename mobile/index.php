<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

/*
 * Mobile app API
 */

require __DIR__ . "/../required.php";

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Allow ping check without authentication
if ($VARS['action'] == "ping") {
    exit(json_encode(["status" => "OK"]));
}

if (MOBILE_ENABLED !== TRUE) {
    exit(json_encode(["status" => "ERROR", "msg" => $Strings->get("mobile login disabled", false)]));
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
    Log::insert(LogType::MOBILE_BAD_KEY, null, "Username: " . $username . ", Key: " . $key);
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
        $user = User::byUsername($username);
        if ($user->getStatus()->get() != AccountStatus::NORMAL) {
            Log::insert(LogType::MOBILE_LOGIN_FAILED, null, "Username: " . $username . ", Key: " . $key);
            exit(json_encode(["status" => "ERROR", "msg" => $Strings->get("login failed try on web", false)]));
        }
        if ($user->checkPassword($VARS['password'])) {
            Log::insert(LogType::MOBILE_LOGIN_OK, $user->getUID(), "Key: " . $key);
            exit(json_encode(["status" => "OK", "uid" => $user->getUID()]));
        } else {
            Log::insert(LogType::MOBILE_LOGIN_FAILED, null, "Username: " . $username . ", Key: " . $key);
            exit(json_encode(["status" => "ERROR", "msg" => $Strings->get("login incorrect", false)]));
        }
    case "user_info":
        engageRateLimit();
        $user = User::byUsername($username);
        if ($user->getStatus()->get() != AccountStatus::NORMAL) {
            Log::insert(LogType::MOBILE_LOGIN_FAILED, null, "Username: " . $username . ", Key: " . $key);
            exit(json_encode(["status" => "ERROR", "msg" => $Strings->get("login failed try on web", false)]));
        }
        if ($user->checkPassword($VARS['password'])) {
            $userinfo = ["uid" => $user->getUID(), "username" => $user->getUsername(), "realname" => $user->getName(), "email" => $user->getEmail()];
            Log::insert(LogType::MOBILE_LOGIN_OK, $user->getUID(), "Key: " . $key);
            exit(json_encode(["status" => "OK", "info" => $userinfo]));
        } else {
            Log::insert(LogType::MOBILE_LOGIN_FAILED, null, "Username: " . $username . ", Key: " . $key);
            exit(json_encode(["status" => "ERROR", "msg" => $Strings->get("login incorrect", false)]));
        }
    case "start_session":
        // Do a web login.
        engageRateLimit();
        $user = User::byUsername($username);
        if ($user->exists()) {
            if ($user->getStatus()->get() == AccountStatus::NORMAL) {
                if ($user->checkPassword($VARS['password'])) {
                    Session::start($user);
                    $_SESSION['mobile'] = true;
                    exit(json_encode(["status" => "OK"]));
                }
            }
        }
        Log::insert(LogType::MOBILE_LOGIN_FAILED, null, "Username: " . $username . ", Key: " . $key);
        exit(json_encode(["status" => "ERROR", "msg" => $Strings->get("login incorrect", false)]));
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
        $user = User::byUsername($username);
        $code = "";
        do {
            $code = random_int(100000, 999999);
        } while ($database->has("onetimekeys", ["key" => $code]));

        $database->insert("onetimekeys", ["key" => $code, "uid" => $user->getUID(), "expires" => date("Y-m-d H:i:s", strtotime("+1 minute"))]);

        $database->delete("onetimekeys", ["expires[<]" => date("Y-m-d H:i:s")]); // cleanup
        exit(json_encode(["status" => "OK", "code" => $code]));
    case "checknotifications":
        if (!empty($VARS['username'])) {
            $user = User::byUsername($VARS['username']);
        } else if (!empty($VARS['uid'])) {
            $user = new User($VARS['uid']);
        } else {
            http_response_code(400);
            die("\"400 Bad Request\"");
        }
        try {
            $notifications = Notifications::get($user, false);
            exit(json_encode(["status" => "OK", "notifications" => $notifications]));
        } catch (Exception $ex) {
            exit(json_encode(["status" => "ERROR", "msg" => $ex->getMessage()]));
        }
        break;
    case "readnotification":
        if (!empty($VARS['username'])) {
            $user = User::byUsername($VARS['username']);
        } else if (!empty($VARS['uid'])) {
            $user = new User($VARS['uid']);
        } else {
            http_response_code(400);
            die("\"400 Bad Request\"");
        }
        if (empty($VARS['id'])) {
            exit(json_encode(["status" => "ERROR", "msg" => $Strings->get("invalid parameters", false)]));
        }
        try {
            Notifications::read($user, $VARS['id']);
            exit(json_encode(["status" => "OK"]));
        } catch (Exception $ex) {
            exit(json_encode(["status" => "ERROR", "msg" => $ex->getMessage()]));
        }
        break;
    case "addnotification":
        if (!empty($VARS['username'])) {
            $user = User::byUsername($VARS['username']);
        } else if (!empty($VARS['uid'])) {
            $user = new User($VARS['uid']);
        } else {
            http_response_code(400);
            die("\"400 Bad Request\"");
        }

        try {
            $timestamp = "";
            if (!empty($VARS['timestamp'])) {
                $timestamp = date("Y-m-d H:i:s", strtotime($VARS['timestamp']));
            }
            $url = "";
            if (!empty($VARS['url'])) {
                $url = $VARS['url'];
            }
            $nid = Notifications::add($user, $VARS['title'], $VARS['content'], $timestamp, $url, isset($VARS['sensitive']));

            exit(json_encode(["status" => "OK", "id" => $nid]));
        } catch (Exception $ex) {
            exit(json_encode(["status" => "ERROR", "msg" => $ex->getMessage()]));
        }
        break;
    case "deletenotification":
        if (!empty($VARS['username'])) {
            $user = User::byUsername($VARS['username']);
        } else if (!empty($VARS['uid'])) {
            $user = new User($VARS['uid']);
        } else {
            http_response_code(400);
            die("\"400 Bad Request\"");
        }

        if (empty($VARS['id'])) {
            exit(json_encode(["status" => "ERROR", "msg" => $Strings->get("invalid parameters", false)]));
        }
        try {
            Notifications::delete($user, $VARS['id']);
            exit(json_encode(["status" => "OK"]));
        } catch (Exception $ex) {
            exit(json_encode(["status" => "ERROR", "msg" => $ex->getMessage()]));
        }
        break;
    default:
        http_response_code(404);
        die(json_encode(["status" => "ERROR", "msg" => "The requested action is not available."]));
}