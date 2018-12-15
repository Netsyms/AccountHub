<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

/**
 * Simple JSON API to allow other apps to access accounts in this system.
 *
 * Requests can be sent via either GET or POST requests.  POST is recommended
 * as it has a lower chance of being logged on the server, exposing unencrypted
 * user passwords.
 */
require __DIR__ . '/required.php';
header("Content-Type: application/json");


if (empty($VARS['key'])) {
    die("\"403 Unauthorized\"");
} else {
    $key = $VARS['key'];
    if ($database->has('apikeys', ['key' => $key]) !== TRUE) {
        engageRateLimit();
        http_response_code(403);
        Log::insert(LogType::API_BAD_KEY, null, "Key: " . $key);
        die("\"403 Unauthorized\"");
    }
}

/**
 * Get the API key with most of the characters replaced with *s.
 * @global string $key
 * @return string
 */
function getCensoredKey() {
    global $key;
    $resp = $key;
    if (strlen($key) > 5) {
        for ($i = 2; $i < strlen($key) - 2; $i++) {
            $resp[$i] = "*";
        }
    }
    return $resp;
}

if (empty($VARS['action'])) {
    http_response_code(404);
    die(json_encode("No action specified."));
}

switch ($VARS['action']) {
    case "ping":
        exit(json_encode(["status" => "OK"]));
        break;
    case "auth":
        $user = User::byUsername($VARS['username']);
        if ($user->checkPassword($VARS['password'])) {
            Log::insert(LogType::API_AUTH_OK, null, "Username: " . strtolower($VARS['username']) . ", Key: " . getCensoredKey());
            exit(json_encode(["status" => "OK", "msg" => $Strings->get("login successful", false)]));
        } else {
            Log::insert(LogType::API_AUTH_FAILED, $user->getUID(), "Username: " . strtolower($VARS['username']) . ", Key: " . getCensoredKey());
            if ($user->exists()) {
                switch ($user->getStatus()->get()) {
                    case AccountStatus::LOCKED_OR_DISABLED:
                        exit(json_encode(["status" => "ERROR", "msg" => $Strings->get("account locked", false)]));
                    case AccountStatus::TERMINATED:
                        exit(json_encode(["status" => "ERROR", "msg" => $Strings->get("account terminated", false)]));
                    case AccountStatus::CHANGE_PASSWORD:
                        exit(json_encode(["status" => "ERROR", "msg" => $Strings->get("password expired", false)]));
                    case AccountStatus::NORMAL:
                        break;
                    default:
                        exit(json_encode(["status" => "ERROR", "msg" => $Strings->get("account state error", false)]));
                }
            }
            exit(json_encode(["status" => "ERROR", "msg" => $Strings->get("login incorrect", false)]));
        }
        break;
    case "userinfo":
        if (!empty($VARS['username'])) {
            $user = User::byUsername($VARS['username']);
        } else if (!empty($VARS['uid']) && is_numeric($VARS['uid'])) {
            $user = new User($VARS['uid']);
        } else {
            http_response_code(400);
            die("\"400 Bad Request\"");
        }
        if ($user->exists()) {
            $data = $database->get("accounts", ["uid", "username", "realname (name)", "email", "phone" => ["phone1 (1)", "phone2 (2)"], 'pin'], ["uid" => $user->getUID()]);
            $data['pin'] = (is_null($data['pin']) || $data['pin'] == "" ? false : true);
            exit(json_encode(["status" => "OK", "data" => $data]));
        } else {
            exit(json_encode(["status" => "ERROR", "msg" => $Strings->get("login incorrect", false)]));
        }
        break;
    case "userexists":
        if (!empty($VARS['uid']) && is_numeric($VARS['uid'])) {
            $user = new User($VARS['uid']);
        } else if (!empty($VARS['username'])) {
            $user = User::byUsername($VARS['username']);
        } else {
            http_response_code(400);
            die("\"400 Bad Request\"");
        }

        exit(json_encode(["status" => "OK", "exists" => $user->exists()]));
        break;
    case "hastotp":
        exit(json_encode(["status" => "OK", "otp" => User::byUsername($VARS['username'])->has2fa()]));
        break;
    case "verifytotp":
        $user = User::byUsername($VARS['username']);
        if ($user->check2fa($VARS['code'])) {
            exit(json_encode(["status" => "OK", "valid" => true]));
        } else {
            Log::insert(LogType::API_BAD_2FA, null, "Username: " . strtolower($VARS['username']) . ", Key: " . getCensoredKey());
            exit(json_encode(["status" => "ERROR", "msg" => $Strings->get("2fa incorrect", false), "valid" => false]));
        }
        break;
    case "acctstatus":
        exit(json_encode(["status" => "OK", "account" => User::byUsername($VARS['username'])->getStatus()->getString()]));
    case "login":
        // simulate a login, checking account status and alerts
        engageRateLimit();
        $user = User::byUsername($VARS['username']);
        if ($user->checkPassword($VARS['password'])) {
            switch ($user->getStatus()->getString()) {
                case "LOCKED_OR_DISABLED":
                    Log::insert(LogType::API_LOGIN_FAILED, $uid, "Username: " . strtolower($VARS['username']) . ", Key: " . getCensoredKey());
                    exit(json_encode(["status" => "ERROR", "msg" => $Strings->get("account locked", false)]));
                case "TERMINATED":
                    Log::insert(LogType::API_LOGIN_FAILED, $uid, "Username: " . strtolower($VARS['username']) . ", Key: " . getCensoredKey());
                    exit(json_encode(["status" => "ERROR", "msg" => $Strings->get("account terminated", false)]));
                case "CHANGE_PASSWORD":
                    Log::insert(LogType::API_LOGIN_FAILED, $uid, "Username: " . strtolower($VARS['username']) . ", Key: " . getCensoredKey());
                    exit(json_encode(["status" => "ERROR", "msg" => $Strings->get("password expired", false)]));
                case "NORMAL":
                    Log::insert(LogType::API_LOGIN_OK, $uid, "Username: " . strtolower($VARS['username']) . ", Key: " . getCensoredKey());
                    exit(json_encode(["status" => "OK"]));
                case "ALERT_ON_ACCESS":
                    $user->sendAlertEmail();
                    Log::insert(LogType::API_LOGIN_OK, $uid, "Username: " . strtolower($VARS['username']) . ", Key: " . getCensoredKey());
                    exit(json_encode(["status" => "OK", "alert" => true]));
                default:
                    Log::insert(LogType::API_LOGIN_FAILED, $uid, "Username: " . strtolower($VARS['username']) . ", Key: " . getCensoredKey());
                    exit(json_encode(["status" => "ERROR", "msg" => $Strings->get("account state error", false)]));
            }
        } else {
            Log::insert(LogType::API_LOGIN_FAILED, null, "Username: " . strtolower($VARS['username']) . ", Key: " . getCensoredKey());
            exit(json_encode(["status" => "ERROR", "msg" => $Strings->get("login incorrect", false)]));
        }
        break;
    case "ismanagerof":
        if ($VARS['uid'] == "1") {
            $manager = new User($VARS['manager']);
            $employee = new User($VARS['employee']);
        } else {
            $manager = User::byUsername($VARS['manager']);
            $employee = User::byUsername($VARS['employee']);
        }
        if (!$manager->exists()) {
            exit(json_encode(["status" => "ERROR", "msg" => $Strings->get("user does not exist", false), "user" => $VARS['manager']]));
        }
        if (!$employee->exists()) {
            exit(json_encode(["status" => "ERROR", "msg" => $Strings->get("user does not exist", false), "user" => $VARS['employee']]));
        }

        if ($database->has('managers', ['AND' => ['managerid' => $manager->getUID(), 'employeeid' => $employee->getUID()]])) {
            exit(json_encode(["status" => "OK", "managerof" => true]));
        } else {
            exit(json_encode(["status" => "OK", "managerof" => false]));
        }
        break;
    case "getmanaged":
        if (!empty($VARS['uid'])) {
            $manager = new User($VARS['uid']);
        } else if (!empty($VARS['username'])) {
            $manager = User::byUsername($VARS['username']);
        } else {
            http_response_code(400);
            die("\"400 Bad Request\"");
        }
        if (!$manager->exists()) {
            exit(json_encode(["status" => "ERROR", "msg" => $Strings->get("user does not exist", false)]));
        }
        if ($VARS['get'] == "username") {
            $managed = $database->select('managers', ['[>]accounts' => ['employeeid' => 'uid']], 'username', ['managerid' => $manager->getUID()]);
        } else {
            $managed = $database->select('managers', 'employeeid', ['managerid' => $manager->getUID()]);
        }
        exit(json_encode(["status" => "OK", "employees" => $managed]));
        break;
    case "getmanagers":
        if (!empty($VARS['uid'])) {
            $emp = new User($VARS['uid']);
        } else if (!empty($VARS['username'])) {
            $emp = User::byUsername($VARS['username']);
        } else {
            http_response_code(400);
            die("\"400 Bad Request\"");
        }
        if (!$emp->exists()) {
            exit(json_encode(["status" => "ERROR", "msg" => $Strings->get("user does not exist", false)]));
        }
        $managers = $database->select('managers', 'managerid', ['employeeid' => $emp->getUID()]);
        exit(json_encode(["status" => "OK", "managers" => $managers]));
        break;
    case "usersearch":
        if (empty($VARS['search']) || strlen($VARS['search']) < 3) {
            exit(json_encode(["status" => "OK", "result" => []]));
        }
        $data = $database->select('accounts', ['uid', 'username', 'realname (name)'], ["OR" => ['username[~]' => $VARS['search'], 'realname[~]' => $VARS['search']], "LIMIT" => 10]);
        exit(json_encode(["status" => "OK", "result" => $data]));
        break;
    case "permission":
        if (empty($VARS['code'])) {
            http_response_code(400);
            die("\"400 Bad Request\"");
        }
        $perm = $VARS['code'];
        if (!empty($VARS['uid'])) {
            $user = new User($VARS['uid']);
        } else if (!empty($VARS['username'])) {
            $user = User::byUsername($VARS['username']);
        } else {
            http_response_code(400);
            die("\"400 Bad Request\"");
        }
        if (!$user->exists()) {
            exit(json_encode(["status" => "ERROR", "msg" => $Strings->get("user does not exist", false)]));
        }
        exit(json_encode(["status" => "OK", "has_permission" => $user->hasPermission($perm)]));
        break;
    case "mobileenabled":
        exit(json_encode(["status" => "OK", "mobile" => MOBILE_ENABLED]));
    case "mobilevalid":
        if (empty($VARS['username']) || empty($VARS['code'])) {
            http_response_code(400);
            die("\"400 Bad Request\"");
        }
        $code = strtoupper($VARS['code']);
        $user_key_valid = $database->has('mobile_codes', ['[>]accounts' => ['uid' => 'uid']], ["AND" => ['mobile_codes.code' => $code, 'accounts.username' => strtolower($VARS['username'])]]);
        exit(json_encode(["status" => "OK", "valid" => $user_key_valid]));
    case "alertemail":
        engageRateLimit();
        if (empty($VARS['username']) || !User::byUsername($VARS['username'])->exists()) {
            http_response_code(400);
            die("\"400 Bad Request\"");
        }
        $appname = "???";
        if (!empty($VARS['appname'])) {
            $appname = $VARS['appname'];
        }
        $result = User::byUsername($VARS['username'])->sendAlertEmail($appname);
        if ($result === TRUE) {
            exit(json_encode(["status" => "OK"]));
        }
        exit(json_encode(["status" => "ERROR", "msg" => $result]));
    case "codelogin":
        $database->delete("onetimekeys", ["expires[<]" => date("Y-m-d H:i:s")]); // cleanup
        if ($database->has("onetimekeys", ["key" => $VARS['code'], "expires[>]" => date("Y-m-d H:i:s")])) {
            $user = $database->get("onetimekeys", ["[>]accounts" => ["uid" => "uid"]], ["username", "realname", "accounts.uid"], ["key" => $VARS['code']]);
            exit(json_encode(["status" => "OK", "user" => $user]));
        } else {
            exit(json_encode(["status" => "ERROR", "msg" => $Strings->get("no such code or code expired", false)]));
        }
    case "listapps":
        $apps = EXTERNAL_APPS;
        // Format paths as absolute URLs
        foreach ($apps as $k => $v) {
            if (strpos($apps[$k]['url'], "http") === FALSE) {
                $apps[$k]['url'] = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . ($_SERVER['SERVER_PORT'] != 80 || $_SERVER['SERVER_PORT'] != 443 ? ":" . $_SERVER['SERVER_PORT'] : "") . $apps[$k]['url'];
            }
        }
        exit(json_encode(["status" => "OK", "apps" => $apps]));
    case "getusersbygroup":
        if ($VARS['gid']) {
            if ($database->has("groups", ['groupid' => $VARS['gid']])) {
                $groupid = $VARS['gid'];
            } else {
                exit(json_encode(["status" => "ERROR", "msg" => $Strings->get("group does not exist", false)]));
            }
        } else {
            http_response_code(400);
            die("\"400 Bad Request\"");
        }
        if ($VARS['get'] == "username") {
            $users = $database->select('assigned_groups', ['[>]accounts' => ['uid' => 'uid']], 'username', ['groupid' => $groupid, "ORDER" => "username"]);
        } else if ($VARS['get'] == "detail") {
            $users = $database->select('assigned_groups', ['[>]accounts' => ['uid' => 'uid']], ['username', 'realname (name)', 'accounts.uid', 'pin'], ['groupid' => $groupid, "ORDER" => "realname"]);
            for ($i = 0; $i < count($users); $i++) {
                if (is_null($users[$i]['pin']) || $users[$i]['pin'] == "") {
                    $users[$i]['pin'] = false;
                } else {
                    $users[$i]['pin'] = true;
                }
            }
        } else {
            $users = $database->select('assigned_groups', 'uid', ['groupid' => $groupid]);
        }
        exit(json_encode(["status" => "OK", "users" => $users]));
        break;
    case "getgroupsbyuser":
        if ($VARS['uid']) {
            if ($database->has("accounts", ['uid' => $VARS['uid']])) {
                $empid = $VARS['uid'];
            } else {
                exit(json_encode(["status" => "ERROR", "msg" => $Strings->get("user does not exist", false)]));
            }
        } else if ($VARS['username']) {
            if ($database->has("accounts", ['username' => strtolower($VARS['username'])])) {
                $empid = $database->select('accounts', 'uid', ['username' => strtolower($VARS['username'])]);
            } else {
                exit(json_encode(["status" => "ERROR", "msg" => $Strings->get("user does not exist", false)]));
            }
        } else {
            http_response_code(400);
            die("\"400 Bad Request\"");
        }
        $groups = $database->select('assigned_groups', ["[>]groups" => ["groupid" => "groupid"]], ['groups.groupid (id)', 'groups.groupname (name)'], ['uid' => $empid]);
        exit(json_encode(["status" => "OK", "groups" => $groups]));
        break;
    case "getgroups":
        $groups = $database->select('groups', ['groupid (id)', 'groupname (name)']);
        exit(json_encode(["status" => "OK", "groups" => $groups]));
        break;
    case "groupsearch":
        if (empty($VARS['search']) || strlen($VARS['search']) < 2) {
            exit(json_encode(["status" => "OK", "result" => []]));
        }
        $data = $database->select('groups', ['groupid (id)', 'groupname (name)'], ['groupname[~]' => $VARS['search'], "LIMIT" => 10]);
        exit(json_encode(["status" => "OK", "result" => $data]));
        break;
    case "checkpin":
        $pin = "";
        if (empty($VARS['pin'])) {
            http_response_code(400);
            die("\"400 Bad Request\"");
        }
        if (!empty($VARS['username'])) {
            $user = User::byUsername($VARS['username']);
        } else if (!empty($VARS['uid'])) {
            $user = new User($VARS['uid']);
        } else {
            http_response_code(400);
            die("\"400 Bad Request\"");
        }
        if ($user->exists()) {
            $pin = $database->get("accounts", "pin", ["uid" => $user->getUID()]);
        } else {
            exit(json_encode(["status" => "ERROR", "msg" => $Strings->get("login incorrect", false)]));
        }
        if (is_null($pin) || $pin == "") {
            exit(json_encode(["status" => "ERROR", "pinvalid" => false, "nopinset" => true]));
        }
        exit(json_encode(["status" => "OK", "pinvalid" => ($pin == $VARS['pin'])]));
        break;
    case "getnotifications":
        if (!empty($VARS['username'])) {
            $user = User::byUsername($VARS['username']);
        } else if (!empty($VARS['uid'])) {
            $user = new User($VARS['uid']);
        } else {
            http_response_code(400);
            die("\"400 Bad Request\"");
        }
        try {
            $notifications = Notifications::get($user);
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
        die(json_encode("404 Not Found: the requested action is not available."));
}
