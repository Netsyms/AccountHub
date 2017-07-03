<?php

dieifnotloggedin();
addMultiLangStrings(["en_us" => [
        "inventory" => "Inventory",
        "open inventory system" => "Open the inventory system"
    ]
]);
$APPS["inventory_link"]["i18n"] = TRUE;
$APPS["inventory_link"]["title"] = "inventory";
$APPS["inventory_link"]["icon"] = "cubes";
$APPS["inventory_link"]["type"] = "teal";
$content = "<p class='mobile-app-hide'>" . lang("open inventory system", false) . '</p><a href="' . INVENTORY_HOME . '" class="btn btn-primary btn-block mobile-app-hide">' . lang("open app", false) . ' &nbsp;<i class="fa fa-external-link-square"></i></a>';
$APPS["inventory_link"]["content"] = $content;
?>