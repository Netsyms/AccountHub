<?php

dieifnotloggedin();
addMultiLangStrings(["en_us" => [
        "qwikclock" => "QwikClock",
        "punch in" => "Punch in",
        "punch out" => "Punch out"
    ]
]);
$APPS["qwikclock_inout"]["i18n"] = TRUE;
$APPS["qwikclock_inout"]["title"] = "qwikclock";
$APPS["qwikclock_inout"]["icon"] = "clock-o";
$content = "";
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
            $content = "<div class=\"alert alert-success\">" . $resp['msg'] . "</div>";
        } else {
            $content = "<div class=\"alert alert-danger\">" . $resp['msg'] . "</div>";
        }
    } catch (Exception $e) {
        $content = "<div class=\"alert alert-danger\">" . lang("error loading widget", false) . "  " . $e->getMessage() . "</div>";
    }
}
$lang_punchin = lang("punch in", false);
$lang_punchout = lang("punch out", false);
$content .= <<<END
                <a href="home.php?&qwikclock=punchin" class="btn btn-block btn-success btn-lg"><i class="fa fa-play"></i> $lang_punchin</a>
                <br />
                <a href="home.php?qwikclock=punchout" class="btn btn-block btn-danger btn-lg"><i class="fa fa-stop"></i> $lang_punchout</a>        
END;
$APPS["qwikclock_inout"]["content"] = $content;
?>