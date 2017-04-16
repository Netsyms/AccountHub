<?php

dieifnotloggedin();

$APPS["404_error"]["title"] = lang("404 error", false);
$APPS["404_error"]["icon"] = "times";
$APPS["404_error"]["type"] = "warning";
$APPS["404_error"]["content"] = "<h4>" . lang("page not found", false) . "</h4>";
?>