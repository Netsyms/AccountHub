<?php

require __DIR__ . "/required.php";
require __DIR__ . "/lib/login.php";

use Endroid\QrCode\QrCode;
use OTPHP\TOTP;

if ($_GET['show'] == '1') {

    $totp = new TOTP(
            "admin@netsyms.com", // The label (string)
            "ZBUJDTW5D5E6KBMDICAJSKRCX6VGQZCZ"  // The secret encoded in base 32 (string)
    );

    echo "Current OTP: " . $totp->now();

    die();
} else {

    $user = "skylarmt";

    $totp = newTOTP($user);

// Create a QR code
    $qrCode = new QrCode($totp);
    $qrCode->setSize(300);

// now we can output the QR code
    header('Content-Type: ' . $qrCode->getContentType(QrCode::IMAGE_TYPE_PNG));
    $qrCode->render(null, 'png');
}