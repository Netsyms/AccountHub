<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

dieifnotloggedin();
addMultiLangStrings(["en_us" => [
        "manage account security" => "Manage account security",
        "manage security description" => "Review security features or change your password."
    ]
]);
$APPS["account_security"]["i18n"] = TRUE;
$APPS["account_security"]["title"] = "account security";
$APPS["account_security"]["icon"] = "lock";
$APPS["account_security"]["type"] = "brown";
$content = "<p>"
        . lang("manage security description", false)
        . '</p> '
        . '<a href="home.php?page=security" class="btn btn-primary btn-block">'
        . lang("manage account security", false)
        . '</a>';
$APPS["account_security"]["content"] = $content;
?>