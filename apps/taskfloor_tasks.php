<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

dieifnotloggedin();
addMultiLangStrings(["en_us" => [
        "tasks" => "Tasks",
        "no tasks found" => "No tasks found."
    ]
]);
$APPS["taskfloor_tasks"]["i18n"] = TRUE;
$APPS["taskfloor_tasks"]["title"] = "tasks";
$APPS["taskfloor_tasks"]["icon"] = "tasks";
$APPS["taskfloor_tasks"]["type"] = "blue-grey";
$content = "";

use GuzzleHttp\Exception\ClientException;

try {
    $client = new GuzzleHttp\Client();

    $response = $client->request('POST', TASKFLOOR_API, ['form_params' => [
            'action' => "gettasks",
            'username' => $_SESSION['username'],
            'password' => $_SESSION['password'],
            'max' => 5
    ]]);

    $resp = json_decode($response->getBody(), TRUE);
    if ($resp['status'] == "OK") {
        if (count($resp['tasks']) > 0) {
            $content = '<div class="list-group">';
            foreach ($resp['tasks'] as $task) {
                $content .= '<div class="list-group-item">';
                $content .= '<i class="fa fa-fw fa-' . $task['icon'] . '"></i> ' . $task['title'] . '';
                $content .= '</div>';
            }
            $content .= "</div>";
        } else {
            $content = "<div class=\"alert alert-success\">" . lang("no tasks found", false) . "</div>";
        }
    }
    $content .= '<a href="' . TASKFLOOR_HOME . '" class="btn btn-primary btn-block mobile-app-hide">' . lang("open app", false) . ' &nbsp;<i class="fa fa-external-link-square"></i></a>';
    $APPS["taskfloor_tasks"]["content"] = $content;
} catch (ClientException $e) {
    if ($e->getResponse()->getStatusCode() == 403) {
        unset($APPS['taskfloor_tasks']);
    }
} catch (Exception $e) {
    $content = "<div class=\"alert alert-danger\">" . lang("error loading widget", false) . "  " . $e->getMessage() . "</div>";
    $content .= '<a href="' . TASKFLOOR_HOME . '" class="btn btn-primary btn-block mobile-app-hide">' . lang("open app", false) . ' &nbsp;<i class="fa fa-external-link-square"></i></a>';
    $APPS["taskfloor_tasks"]["content"] = $content;
}
?>