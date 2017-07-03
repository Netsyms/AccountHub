<?php

dieifnotloggedin();
require_once __DIR__ . "/../lib/login.php";
addMultiLangStrings(["en_us" => [
        "qwikclock" => "QwikClock",
        "punch in" => "Punch in",
        "punch out" => "Punch out",
        "permission denied" => "You do not have permission to do that."
    ]
]);
$APPS["qwikclock_inout"]["i18n"] = TRUE;
$APPS["qwikclock_inout"]["title"] = "qwikclock";
$APPS["qwikclock_inout"]["icon"] = "clock-o";
$APPS["qwikclock_inout"]["type"] = "blue";
$content = "";

use GuzzleHttp\Exception\ClientException;

if (!is_empty($_GET['qwikclock']) && ($_GET['qwikclock'] === "punchin" || $_GET['qwikclock'] === "punchout")) {
    try {
        $client = new GuzzleHttp\Client();

        $response = $client->request('POST', QWIKCLOCK_API, ['form_params' => [
                'action' => $_GET['qwikclock'],
                'username' => $_SESSION['username'],
                'password' => $_SESSION['password']
        ]]);

        $resp = json_decode($response->getBody(), TRUE);
        if ($resp['status'] == "OK") {
            $content = "<div class=\"alert alert-success alert-dismissable\"><button type=\"button\" class=\"close\">&times;</button>" . $resp['msg'] . "</div>";
        } else {
            $content = "<div class=\"alert alert-danger alert-dismissable\"><button type=\"button\" class=\"close\">&times;</button>" . $resp['msg'] . "</div>";
        }
    } catch (ClientException $e) {
        if ($e->getResponse()->getStatusCode() == 403) {
            $content = "<div class=\"alert alert-danger alert-dismissable\"><button type=\"button\" class=\"close\">&times;</button>" . lang("permission denied", false) . "</div>";
        }
    } catch (Exception $e) {
        $content = "<div class=\"alert alert-danger alert-dismissable\"><button type=\"button\" class=\"close\">&times;</button>" . lang("error loading widget", false) . "  " . $e->getMessage() . "</div>";
    }
}
$lang_punchin = lang("punch in", false);
$lang_punchout = lang("punch out", false);
$content .= <<<END
                <a href="home.php?&qwikclock=punchin" class="btn btn-block btn-success btn-lg"><i class="fa fa-play"></i> $lang_punchin</a>
                <a href="home.php?qwikclock=punchout" class="btn btn-block btn-danger btn-lg"><i class="fa fa-stop"></i> $lang_punchout</a>        
END;
$content .= '<br /><a href="' . QWIKCLOCK_HOME . '" class="btn btn-primary btn-block mobile-app-hide">' . lang("open app", false) . ' &nbsp;<i class="fa fa-external-link-square"></i></a>';
$APPS["qwikclock_inout"]["content"] = $content;


if (account_has_permission($_SESSION['username'], "QWIKCLOCK") !== true) {
    unset($APPS['qwikclock_inout']);
}
?>