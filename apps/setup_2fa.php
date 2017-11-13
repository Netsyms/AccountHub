<?php

dieifnotloggedin();

use OTPHP\Factory;
use Endroid\QrCode\QrCode;

// extra login utils
require_once __DIR__ . "/../lib/login.php";

$APPS["setup_2fa"]["title"] = lang("setup 2fa", false);
$APPS["setup_2fa"]["icon"] = "lock";
if (userHasTOTP($_SESSION['username'])) {
    $APPS["setup_2fa"]["content"] = '<div class="alert alert-info"><i class="fa fa-info-circle"></i> ' . lang("2fa active", false) . '</div>'
            . '<a href="action.php?action=rm2fa&source=security" class="btn btn-warning btn-sm btn-block">'
            . lang("remove 2fa", false) . '</a>';
} else if ($_GET['2fa'] == "generate") {
    $codeuri = newTOTP($_SESSION['username']);
    $qrCode = new QrCode($codeuri);
    $qrCode->setSize(200);
    $qrCode->setErrorCorrection("H");
    $qrcode = $qrCode->getDataUri();
    $totp = Factory::loadFromProvisioningUri($codeuri);
    $codesecret = $totp->getSecret();
    $chunk_secret = trim(chunk_split($codesecret, 8, ' '));
    $APPS["setup_2fa"]["content"] = '<div class="alert alert-info"><i class="fa fa-info-circle"></i> ' . lang("scan 2fa qrcode", false) . '</div>' . <<<END
<style nonce="$SECURE_NONCE">
.mono-chunk {
    text-align: center;
    font-size: 110%;
    font-family: monospace;
}
</style>
<img src="$qrcode" class="img-responsive qrcode" />
<div class="well well-sm mono-chunk">$chunk_secret</div>
<form action="action.php" method="POST">
    <input type="hidden" name="action" value="add2fa" />
    <input type="hidden" name="source" value="security" />
    <input type="hidden" name="secret" value="$codesecret" />
    <button type="submit" class="btn btn-success btn-sm btn-block">
END
            . lang("confirm 2fa", false) . <<<END
    </button>
</form>
END;
} else {
    $APPS["setup_2fa"]["content"] = '<div class="alert alert-info"><i class="fa fa-info-circle"></i> ' . lang("2fa explained", false) . '</div>'
            . '<a class="btn btn-success btn-sm btn-block" href="home.php?page=security&2fa=generate">'
            . lang("enable 2fa", false) . '</a>';
}