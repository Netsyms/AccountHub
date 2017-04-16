<?php

dieifnotloggedin();

$APPS["change_password"]["title"] = "Change Password";
$APPS["change_password"]["icon"] = "key";
$APPS["change_password"]["content"] = <<<CONTENTEND
<form action="action.php" method="POST">
    <input type="password" class="form-control" name="oldpass" placeholder="Old password" />
    <input type="password" class="form-control" name="newpass" placeholder="New password" />
    <input type="password" class="form-control" name="conpass" placeholder="New password (again)" />
    <input type="hidden" name="action" value="chpasswd" />
    <input type="hidden" name="source" value="security" />
    <br />
    <button type="submit" class="btn btn-success btn-sm btn-block">Change Password</button>
</form>
CONTENTEND;
