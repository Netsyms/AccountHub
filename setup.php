<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

/*
 * This script will create a local administrator account.
 */

require __DIR__ . '/required.php';

if ($database->has('accounts', ["[>]assigned_permissions" => ["uid" => "uid"]], ['permid' => 1])) {
    die("An admin account already exists, exiting.");
}

if (is_empty($_POST['username']) || is_empty($_POST['password']) || is_empty($_POST['realname'])) {
    ?>
    <!DOCTYPE html>
    <title>Admin Account Creation</title>
    <h1>Admin Account Creation tool</h1>
    <form action="setup.php" method="POST">
        Username: <input type="text" name="username" placeholder="Username" required="required" /><br />
        Password: <input type="text" name="password" placeholder="Password" required="required" /><br />
        Name: <input type="text" name="realname" placeholder="Real Name" required="required" /><br />
        Email: <input type="email" name="email" placeholder="Email Address" /><br />
        <button type="submit">
            Create account
        </button>
    </form>
    <?php
} else {
    header("Content-Type: text/plain");
    $user = User::byUsername($_POST['username']);
    if ($user->exists()) {
        $userid = $user->getID();
        echo "User already exists, skipping creation.\n";
    } else {
        $userid = User::add($_POST['username'], $_POST['password'], $_POST['realname'], (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ? $_POST['email'] : null), "", "", 1);
        echo "User account #$userid created.\n";
    }
    $database->insert('assigned_permissions', ['uid' => $userid, 'permid' => 1]);
    die("ADMIN permission assigned.");
}
