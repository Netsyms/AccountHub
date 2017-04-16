<?php

use Base32\Base32;
use OTPHP\TOTP;

/**
 * Send an alert email to the system admin
 * 
 * Used when an account with the status ALERT_ON_ACCESS logs in
 * @param String $username the account username
 */
function sendAlertEmail($username) {
    // TODO: add email code
}

/**
 * Setup $_SESSION values to log in a user
 * @param string $username
 */
function doLoginUser($username) {
    global $database;
    $userinfo = $database->select('accounts', ['email', 'uid', 'realname'], ['username' => $username])[0];
    $_SESSION['username'] = $username;
    $_SESSION['uid'] = $userinfo['uid'];
    $_SESSION['email'] = $userinfo['email'];
    $_SESSION['realname'] = $userinfo['realname'];
    $_SESSION['loggedin'] = true;
}

/**
 * Check if a user has TOTP setup
 * @global $database $database
 * @param string $username
 * @return boolean true if TOTP secret exists, else false
 */
function userHasTOTP($username) {
    global $database;
    $secret = $database->select('accounts', 'authsecret', ['username' => $username])[0];
    if (is_empty($secret)) {
        return false;
    }
    return true;
}

/**
 * Generate a TOTP secret for the given user.
 * @param string $username
 * @return string OTP provisioning URI (for generating a QR code)
 */
function newTOTP($username) {
    global $database;
    $secret = random_bytes(20);
    $encoded_secret = Base32::encode($secret);
    $userdata = $database->select('accounts', ['email', 'authsecret'], ['username' => $username])[0];
    $totp = new TOTP($userdata['email'], $encoded_secret);
    $totp->setIssuer(SYSTEM_NAME);
    return $totp->getProvisioningUri();
}

/**
 * Save a TOTP secret for the user.
 * @global $database $database
 * @param string $username
 * @param string $secret
 */
function saveTOTP($username, $secret) {
    global $database;
    $database->update('accounts', ['authsecret' => $secret], ['username' => $username]);
}

/**
 * Verify a TOTP multiauth code
 * @global $database
 * @param string $username
 * @param int $code
 * @return boolean true if it's legit, else false
 */
function verifyTOTP($username, $code) {
    global $database;
    $userdata = $database->select('accounts', ['email', 'authsecret'], ['username' => $username])[0];
    if (is_empty($userdata['authsecret'])) {
        return false;
    }
    $totp = new TOTP(null, $userdata['authsecret']);
    return $totp->verify($code);
}
