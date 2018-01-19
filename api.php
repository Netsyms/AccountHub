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
require_once __DIR__ . '/lib/login.php';
header("Content-Type: application/json");

//try {
$key = $VARS['key'];
if ($database->has('apikeys', ['key' => $key]) !== TRUE) {
    engageRateLimit();
    http_response_code(403);
    insertAuthLog(14, null, "Key: " . $key);
    die("\"403 Unauthorized\"");
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

switch ($VARS['action']) {
    case "ping":
        exit(json_encode(["status" => "OK"]));
        break;
    case "auth":
        $errmsg = "";
        if (authenticate_user($VARS['username'], $VARS['password'], $errmsg)) {
            insertAuthLog(12, null, "Username: " . strtolower($VARS['username']) . ", Key: " . getCensoredKey());
            exit(json_encode(["status" => "OK", "msg" => lang("login successful", false)]));
        } else {
            insertAuthLog(13, $uid, "Username: " . strtolower($VARS['username']) . ", Key: " . getCensoredKey());
            if (!is_empty($errmsg)) {
                exit(json_encode(["status" => "ERROR", "msg" => lang2("ldap error", ['error' => $errmsg], false)]));
            }
            if (user_exists($VARS['username'])) {
                switch (get_account_status($VARS['username'])) {
                    case "LOCKED_OR_DISABLED":
                        exit(json_encode(["status" => "ERROR", "msg" => lang("account locked", false)]));
                    case "TERMINATED":
                        exit(json_encode(["status" => "ERROR", "msg" => lang("account terminated", false)]));
                    case "CHANGE_PASSWORD":
                        exit(json_encode(["status" => "ERROR", "msg" => lang("password expired", false)]));
                    case "NORMAL":
                        break;
                    default:
                        exit(json_encode(["status" => "ERROR", "msg" => lang("account state error", false)]));
                }
            }
            exit(json_encode(["status" => "ERROR", "msg" => lang("login incorrect", false)]));
        }
        break;
    case "userinfo":
        if (!is_empty($VARS['username'])) {
            if (user_exists_local($VARS['username'])) {
                $data = $database->select("accounts", ["uid", "username", "realname (name)", "email", "phone" => ["phone1 (1)", "phone2 (2)"], 'pin'], ["username" => strtolower($VARS['username'])])[0];
                $data['pin'] = (is_null($data['pin']) || $data['pin'] == "" ? false : true);
                exit(json_encode(["status" => "OK", "data" => $data]));
            } else {
                exit(json_encode(["status" => "ERROR", "msg" => lang("login incorrect", false)]));
            }
        } else if (!is_empty($VARS['uid'])) {
            if ($database->has('accounts', ['uid' => $VARS['uid']])) {
                $data = $database->select("accounts", ["uid", "username", "realname (name)", "email", "phone" => ["phone1 (1)", "phone2 (2)"], 'pin'], ["uid" => $VARS['uid']])[0];
                $data['pin'] = (is_null($data['pin']) || $data['pin'] == "" ? false : true);
                exit(json_encode(["status" => "OK", "data" => $data]));
            } else {
                exit(json_encode(["status" => "ERROR", "msg" => lang("login incorrect", false)]));
            }
        } else {
            http_response_code(400);
            die("\"400 Bad Request\"");
        }
        break;
    case "userexists":
        if (!is_empty($VARS['uid'])) {
            if ($database->has('accounts', ['uid' => $VARS['uid']])) {
                exit(json_encode(["status" => "OK", "exists" => true]));
            } else {
                exit(json_encode(["status" => "OK", "exists" => false]));
            }
        }
        if (user_exists_local($VARS['username'])) {
            exit(json_encode(["status" => "OK", "exists" => true]));
        } else {
            exit(json_encode(["status" => "OK", "exists" => false]));
        }
        break;
    case "hastotp":
        if (userHasTOTP($VARS['username'])) {
            exit(json_encode(["status" => "OK", "otp" => true]));
        } else {
            exit(json_encode(["status" => "OK", "otp" => false]));
        }
        break;
    case "verifytotp":
        if (verifyTOTP($VARS['username'], $VARS['code'])) {
            exit(json_encode(["status" => "OK", "valid" => true]));
        } else {
            insertAuthLog(7, null, "Username: " . strtolower($VARS['username']) . ", Key: " . getCensoredKey());
            exit(json_encode(["status" => "ERROR", "msg" => lang("2fa incorrect", false), "valid" => false]));
        }
        break;
    case "acctstatus":
        exit(json_encode(["status" => "OK", "account" => get_account_status($VARS['username'])]));
    case "login":
        engageRateLimit();
        // simulate a login, checking account status and alerts
        $errmsg = "";
        if (authenticate_user($VARS['username'], $VARS['password'], $errmsg)) {
            $uid = $database->select('accounts', 'uid', ['username' => strtolower($VARS['username'])])[0];
            switch (get_account_status($VARS['username'])) {
                case "LOCKED_OR_DISABLED":
                    insertAuthLog(5, $uid, "Username: " . strtolower($VARS['username']) . ", Key: " . getCensoredKey());
                    exit(json_encode(["status" => "ERROR", "msg" => lang("account locked", false)]));
                case "TERMINATED":
                    insertAuthLog(5, $uid, "Username: " . strtolower($VARS['username']) . ", Key: " . getCensoredKey());
                    exit(json_encode(["status" => "ERROR", "msg" => lang("account terminated", false)]));
                case "CHANGE_PASSWORD":
                    insertAuthLog(5, $uid, "Username: " . strtolower($VARS['username']) . ", Key: " . getCensoredKey());
                    exit(json_encode(["status" => "ERROR", "msg" => lang("password expired", false)]));
                case "NORMAL":
                    insertAuthLog(4, $uid, "Username: " . strtolower($VARS['username']) . ", Key: " . getCensoredKey());
                    exit(json_encode(["status" => "OK"]));
                case "ALERT_ON_ACCESS":
                    sendLoginAlertEmail($VARS['username']);
                    insertAuthLog(4, $uid, "Username: " . strtolower($VARS['username']) . ", Key: " . getCensoredKey());
                    exit(json_encode(["status" => "OK", "alert" => true]));
                default:
                    insertAuthLog(5, $uid, "Username: " . strtolower($VARS['username']) . ", Key: " . getCensoredKey());
                    exit(json_encode(["status" => "ERROR", "msg" => lang("account state error", false)]));
            }
        } else {
            insertAuthLog(5, null, "Username: " . strtolower($VARS['username']) . ", Key: " . getCensoredKey());
            if (!is_empty($errmsg)) {
                exit(json_encode(["status" => "ERROR", "msg" => lang2("ldap error", ['error' => $errmsg], false)]));
            }
            exit(json_encode(["status" => "ERROR", "msg" => lang("login incorrect", false)]));
        }
        break;
    case "ismanagerof":
        if ($VARS['uid'] == "1") {
            if ($database->has("accounts", ['uid' => $VARS['manager']])) {
                if ($database->has("accounts", ['uid' => $VARS['employee']])) {
                    $managerid = $VARS['manager'];
                    $employeeid = $VARS['employee'];
                } else {
                    exit(json_encode(["status" => "ERROR", "msg" => lang("user does not exist", false), "user" => $VARS['employee']]));
                }
            } else {
                exit(json_encode(["status" => "ERROR", "msg" => lang("user does not exist", false), "user" => $VARS['manager']]));
            }
        } else {
            if (user_exists_local($VARS['manager'])) {
                if (user_exists_local($VARS['employee'])) {
                    $managerid = $database->select('accounts', 'uid', ['username' => strtolower($VARS['manager'])]);
                    $employeeid = $database->select('accounts', 'uid', ['username' => strtolower($VARS['employee'])]);
                } else {
                    exit(json_encode(["status" => "ERROR", "msg" => lang("user does not exist", false), "user" => strtolower($VARS['employee'])]));
                }
            } else {
                exit(json_encode(["status" => "ERROR", "msg" => lang("user does not exist", false), "user" => strtolower($VARS['manager'])]));
            }
        }
        if ($database->has('managers', ['AND' => ['managerid' => $managerid, 'employeeid' => $employeeid]])) {
            exit(json_encode(["status" => "OK", "managerof" => true]));
        } else {
            exit(json_encode(["status" => "OK", "managerof" => false]));
        }
        break;
    case "getmanaged":
        if ($VARS['uid']) {
            if ($database->has("accounts", ['uid' => $VARS['uid']])) {
                $managerid = $VARS['uid'];
            } else {
                exit(json_encode(["status" => "ERROR", "msg" => lang("user does not exist", false)]));
            }
        } else if ($VARS['username']) {
            if ($database->has("accounts", ['username' => strtolower($VARS['username'])])) {
                $managerid = $database->select('accounts', 'uid', ['username' => strtolower($VARS['username'])]);
            } else {
                exit(json_encode(["status" => "ERROR", "msg" => lang("user does not exist", false)]));
            }
        } else {
            http_response_code(400);
            die("\"400 Bad Request\"");
        }
        if ($VARS['get'] == "username") {
            $managed = $database->select('managers', ['[>]accounts' => ['employeeid' => 'uid']], 'username', ['managerid' => $managerid]);
        } else {
            $managed = $database->select('managers', 'employeeid', ['managerid' => $managerid]);
        }
        exit(json_encode(["status" => "OK", "employees" => $managed]));
        break;
    case "getmanagers":
        if ($VARS['uid']) {
            if ($database->has("accounts", ['uid' => $VARS['uid']])) {
                $empid = $VARS['uid'];
            } else {
                exit(json_encode(["status" => "ERROR", "msg" => lang("user does not exist", false)]));
            }
        } else if ($VARS['username']) {
            if ($database->has("accounts", ['username' => strtolower($VARS['username'])])) {
                $empid = $database->select('accounts', 'uid', ['username' => strtolower($VARS['username'])]);
            } else {
                exit(json_encode(["status" => "ERROR", "msg" => lang("user does not exist", false)]));
            }
        } else {
            http_response_code(400);
            die("\"400 Bad Request\"");
        }
        $managers = $database->select('managers', 'managerid', ['employeeid' => $empid]);
        exit(json_encode(["status" => "OK", "managers" => $managers]));
        break;
    case "usersearch":
        if (is_empty($VARS['search']) || strlen($VARS['search']) < 3) {
            exit(json_encode(["status" => "OK", "result" => []]));
        }
        $data = $database->select('accounts', ['uid', 'username', 'realname (name)'], ["OR" => ['username[~]' => $VARS['search'], 'realname[~]' => $VARS['search']], "LIMIT" => 10]);
        exit(json_encode(["status" => "OK", "result" => $data]));
        break;
    case "permission":
        if (is_empty($VARS['code'])) {
            http_response_code(400);
            die("\"400 Bad Request\"");
        }
        $perm = $VARS['code'];
        if ($VARS['uid']) {
            if ($database->has("accounts", ['uid' => $VARS['uid']])) {
                $user = $database->select('accounts', ['username'], ['uid' => $VARS['uid']])[0]['username'];
            } else {
                exit(json_encode(["status" => "ERROR", "msg" => lang("user does not exist", false)]));
            }
        } else if ($VARS['username']) {
            if ($database->has("accounts", ['username' => strtolower($VARS['username'])])) {
                $user = $VARS['username'];
            } else {
                exit(json_encode(["status" => "ERROR", "msg" => lang("user does not exist", false)]));
            }
        } else {
            http_response_code(400);
            die("\"400 Bad Request\"");
        }
        $hasperm = account_has_permission($user, $perm);
        exit(json_encode(["status" => "OK", "has_permission" => $hasperm]));
        break;
    case "mobileenabled":
        exit(json_encode(["status" => "OK", "mobile" => MOBILE_ENABLED]));
    case "mobilevalid":
        if (is_empty($VARS['username']) || is_empty($VARS['code'])) {
            http_response_code(400);
            die("\"400 Bad Request\"");
        }
        $code = strtoupper($VARS['code']);
        $user_key_valid = $database->has('mobile_codes', ['[>]accounts' => ['uid' => 'uid']], ["AND" => ['mobile_codes.code' => $code, 'accounts.username' => strtolower($VARS['username'])]]);
        exit(json_encode(["status" => "OK", "valid" => $user_key_valid]));
    case "alertemail":
        engageRateLimit();
        if (is_empty($VARS['username']) || !user_exists($VARS['username'])) {
            http_response_code(400);
            die("\"400 Bad Request\"");
        }
        $appname = "???";
        if (!is_empty($VARS['appname'])) {
            $appname = $VARS['appname'];
        }
        $result = sendLoginAlertEmail($VARS['username'], $appname);
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
            exit(json_encode(["status" => "ERROR", "msg" => lang("no such code or code expired", false)]));
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
                exit(json_encode(["status" => "ERROR", "msg" => lang("group does not exist", false)]));
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
                exit(json_encode(["status" => "ERROR", "msg" => lang("user does not exist", false)]));
            }
        } else if ($VARS['username']) {
            if ($database->has("accounts", ['username' => strtolower($VARS['username'])])) {
                $empid = $database->select('accounts', 'uid', ['username' => strtolower($VARS['username'])]);
            } else {
                exit(json_encode(["status" => "ERROR", "msg" => lang("user does not exist", false)]));
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
        if (is_empty($VARS['search']) || strlen($VARS['search']) < 2) {
            exit(json_encode(["status" => "OK", "result" => []]));
        }
        $data = $database->select('groups', ['groupid (id)', 'groupname (name)'], ['groupname[~]' => $VARS['search'], "LIMIT" => 10]);
        exit(json_encode(["status" => "OK", "result" => $data]));
        break;
    case "checkpin":
        $pin = "";
        if (is_empty($VARS['pin'])) {
            http_response_code(400);
            die("\"400 Bad Request\"");
        }
        if (!is_empty($VARS['username'])) {
            if (user_exists_local($VARS['username'])) {
                $pin = $database->get("accounts", "pin", ["username" => strtolower($VARS['username'])]);
            } else {
                exit(json_encode(["status" => "ERROR", "msg" => lang("login incorrect", false)]));
            }
        } else if (!is_empty($VARS['uid'])) {
            if ($database->has('accounts', ['uid' => $VARS['uid']])) {
                $pin = $database->get("accounts", "pin", ["uid" => strtolower($VARS['uid'])]);
            } else {
                exit(json_encode(["status" => "ERROR", "msg" => lang("login incorrect", false)]));
            }
        } else {
            http_response_code(400);
            die("\"400 Bad Request\"");
        }
        if (is_null($pin) || $pin == "") {
            exit(json_encode(["status" => "ERROR", "pinvalid" => false, "nopinset" => true]));
        }
        exit(json_encode(["status" => "OK", "pinvalid" => ($pin == $VARS['pin'])]));
        break;
    default:
        http_response_code(404);
        die(json_encode("404 Not Found: the requested action is not available."));
}
    /* } catch (Exception $e) {
      header("HTTP/1.1 500 Internal Server Error");
      die("\"500 Internal Server Error\"");
      } */