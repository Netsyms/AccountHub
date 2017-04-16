<?php

dieifnotloggedin();

// extra login utils
require_once __DIR__ . "/../lib/login.php";

$APPS["setup_2fa"]["title"] = lang("setup 2fa", false);
$APPS["setup_2fa"]["icon"] = "lock";
if (userHasTOTP($_SESSION['username'])) {
    $APPS["setup_2fa"]["content"] = '<a href="action.php?action=rm2fa&source=security" class="btn btn-warning">'
            . lang("remove 2fa", false) . '</a>';
} else {
    $APPS["setup_2fa"]["content"] = '<div class="alert alert-info"><i class="fa fa-info-circle"></i> ' . lang("2fa explained", false) . '</div>'
            . '<button class="btn btn-success">'
            . lang("enable 2fa", false) . '</button>';
}