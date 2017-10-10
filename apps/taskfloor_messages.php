<?php

dieifnotloggedin();
addMultiLangStrings(["en_us" => [
        "messages" => "Messages",
        "no messages" => "No messages found."
    ]
]);
$APPS["taskfloor_messages"]["i18n"] = TRUE;
$APPS["taskfloor_messages"]["title"] = "messages";
$APPS["taskfloor_messages"]["icon"] = "comments";
$APPS["taskfloor_messages"]["type"] = "deep-purple";
$content = "";

use GuzzleHttp\Exception\ClientException;
try {
    $client = new GuzzleHttp\Client();

    $response = $client->request('POST', TASKFLOOR_API, ['form_params' => [
            'action' => "getmsgs",
            'username' => $_SESSION['username'],
            'password' => $_SESSION['password'],
            'max' => 5
    ]]);

    $resp = json_decode($response->getBody(), TRUE);
    if ($resp['status'] == "OK") {
        if (count($resp['messages']) > 0) {
            $content = '<div class="list-group">';
            foreach ($resp['messages'] as $msg) {
                $content .= '<div class="list-group-item">';
                $content .= $msg['text'];
                $fromuser = $msg['from']['username'];
                $fromname = $msg['from']['name'];
                $touser = $msg['to']['username'];
                $toname = $msg['to']['name'];
                $content .= <<<END
<br />
<span class="small">
    <span data-toggle="tooltip" title="$fromuser">$fromname</span>
    <i class="fa fa-caret-right"></i>
    <span data-toggle="tooltip" title="$touser">$toname</span>
</span>
END;
                $content .= '</div>';
            }
            $content .= "</div>";
        } else {
            $content = "<div class=\"alert alert-info\">" . lang("no messages", false) . "</div>";
        }
    }
    $content .= '<a href="' . TASKFLOOR_HOME . '" class="btn btn-primary btn-block mobile-app-hide">' . lang("open app", false) . ' &nbsp;<i class="fa fa-external-link-square"></i></a>';
    $APPS["taskfloor_messages"]["content"] = $content;
} catch (ClientException $e) {
    if ($e->getResponse()->getStatusCode() == 403) {
        unset($APPS['taskfloor_messages']);
    }
} catch (Exception $e) {
    $content = "<div class=\"alert alert-danger\">" . lang("error loading widget", false) . "  " . $e->getMessage() . "</div>";
    $content .= '<a href="' . TASKFLOOR_HOME . '" class="btn btn-primary btn-block mobile-app-hide">' . lang("open app", false) . ' &nbsp;<i class="fa fa-external-link-square"></i></a>';
    $APPS["taskfloor_messages"]["content"] = $content;
}
?>