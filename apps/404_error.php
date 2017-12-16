<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

dieifnotloggedin();

$APPS["404_error"]["title"] = lang("404 error", false);
$APPS["404_error"]["icon"] = "times";
$APPS["404_error"]["type"] = "warning";
$APPS["404_error"]["content"] = "<h4>" . lang("page not found", false) . "</h4>";
?>