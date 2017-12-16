<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

dieifnotloggedin();

// Additional i18n strings
addMultiLangStrings(["en_us" => [
        "sample app" => "Sample Application",
    ]
]);
// Set to true to automatically parse the app title as a language string.
$APPS["sample_app"]["i18n"] = TRUE;
// App title.
$APPS["sample_app"]["title"] = "sample app";
// App icon, from FontAwesome.
$APPS["sample_app"]["icon"] = "rocket";
// App content.
$APPS["sample_app"]["content"] = <<<'CONTENTEND'
<div class="list-group">
    <div class="list-group-item">
        Item 1
    </div>
    <div class="list-group-item">
        Item 2
    </div>
    <div class="list-group-item">
        Item 3
    </div>
</div>
CONTENTEND;
?>