<?php

dieifnotloggedin();
addMultiLangStrings(["en_us" => [
        "manage account security" => "Manage account security",
        "manage security description" => "Review security features or change your password."
    ]
]);
$APPS["account_security"]["i18n"] = TRUE;
$APPS["account_security"]["title"] = "account security";
$APPS["account_security"]["icon"] = "lock";
$content = "<p>"
        . lang("manage security description", false)
        . '</p> '
        . '<a href="home.php?page=security" class="btn btn-primary btn-block">'
        . lang("manage account security", false)
        . '</a>';
$APPS["account_security"]["content"] = $content;
?>