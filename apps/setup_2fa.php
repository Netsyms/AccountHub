<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

dieifnotloggedin();

use OTPHP\Factory;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;

// extra login utils
require_once __DIR__ . "/../lib/login.php";

$APPS["setup_2fa"]["title"] = $Strings->get("setup 2fa", false);
$APPS["setup_2fa"]["icon"] = "lock";
if (userHasTOTP($_SESSION['username'])) {
    $APPS["setup_2fa"]["content"] = '<div class="alert alert-info"><i class="fa fa-info-circle"></i> ' . $Strings->get("2fa active", false) . '</div>'
            . '<a href="action.php?action=rm2fa&source=security" class="btn btn-warning btn-sm btn-block">'
            . $Strings->get("remove 2fa", false) . '</a>';
} else if ($_GET['2fa'] == "generate") {
    $codeuri = newTOTP($_SESSION['username']);
    $userdata = $database->select('accounts', ['email', 'authsecret', 'realname'], ['username' => $_SESSION['username']])[0];
    $label = SYSTEM_NAME . ":" . is_null($userdata['email']) ? $userdata['realname'] : $userdata['email'];
    $issuer = SYSTEM_NAME;
    $qrCode = new QrCode($codeuri);
    $qrCode->setWriterByName('svg');
    $qrCode->setSize(550);
    $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH);
    $qrcode = $qrCode->writeDataUri();
    $totp = Factory::loadFromProvisioningUri($codeuri);
    $codesecret = $totp->getSecret();
    $chunk_secret = trim(chunk_split($codesecret, 4, ' '));
    $lang_manualsetup = $Strings->get("manual setup", false);
    $lang_secretkey = $Strings->get("secret key", false);
    $lang_label = $Strings->get("label", false);
    $lang_issuer = $Strings->get("issuer", false);
    $lang_entercode = $Strings->get("enter otp code", false);
    $APPS["setup_2fa"]["content"] = '<div class="alert alert-info"><i class="fa fa-info-circle"></i> ' . $Strings->get("scan 2fa qrcode", false) . '</div>' . <<<END
<style nonce="$SECURE_NONCE">
.margintop-15px {
    margin-top: 15px;
}
.mono-chunk {
    text-align: center;
    font-size: 110%;
    font-family: monospace;
}
</style>
<img src="$qrcode" class="img-responsive qrcode" />
<form action="action.php" method="POST" class="margintop-15px">
    <input type="text" name="totpcode" class="form-control" placeholder="$lang_entercode" minlength=6 maxlength=6 required />
    <br />
    <input type="hidden" name="action" value="add2fa" />
    <input type="hidden" name="source" value="security" />
    <input type="hidden" name="secret" value="$codesecret" />
    <button type="submit" class="btn btn-success btn-sm btn-block">
END
            . $Strings->get("confirm 2fa", false) . <<<END
    </button>
</form>
<div class="panel panel-default margintop-15px">
    <div class="panel-body">
        <b>$lang_manualsetup</b>
        <br /><label>$lang_secretkey:</label>
        <div class="well well-sm mono-chunk">$chunk_secret</div>
        <br /><label>$lang_label:</label>
        <div class="well well-sm mono-chunk">$label</div>
        <br /><label>$lang_issuer:</label>
        <div class="well well-sm mono-chunk">$issuer</div>
    </div>
</div>
END;
} else {
    $APPS["setup_2fa"]["content"] = '<div class="alert alert-info"><i class="fa fa-info-circle"></i> ' . $Strings->get("2fa explained", false) . '</div>'
            . '<a class="btn btn-success btn-sm btn-block" href="home.php?page=security&2fa=generate">'
            . $Strings->get("enable 2fa", false) . '</a>';
}