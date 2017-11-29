<?php
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
    require_once __DIR__ . "/lib/login.php";
    header("Content-Type: text/plain");
    if (user_exists($_POST['username'])) {
        $userid = $database->get('accounts', 'uid', ['username' => $_POST['username']]);
        echo "User already exists, skipping creation.\n";
    } else {
        $userid = adduser($_POST['username'], $_POST['password'], $_POST['realname'], (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ? $_POST['email'] : null), "", "", 1);
        echo "User account #$userid created.\n";
    }
    $database->insert('assigned_permissions', ['uid' => $userid, 'permid' => 1]);
    die("ADMIN permission assigned.");
}