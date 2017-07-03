<?php

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
} catch (Exception $e) {
    $content = "<div class=\"alert alert-danger\">" . lang("error loading widget", false) . "  " . $e->getMessage() . "</div>";
}
$content .= '<a href="' . TASKFLOOR_HOME . '" class="btn btn-primary btn-block mobile-app-hide">' . lang("open app", false) . ' &nbsp;<i class="fa fa-external-link-square"></i></a>';
$APPS["taskfloor_tasks"]["content"] = $content;
?>