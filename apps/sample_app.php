<?php

dieifnotloggedin();

$APPS["sample_app"]["title"] = "Sample App";
$APPS["sample_app"]["icon"] = "rocket";
$APPS["sample_app"]["content"] = <<<'CONTENTEND'
<div class="list-group">
    <div class="list-group-item">
        Item 1
    </div>
    <div class="list-group-item">
        Item 2
    </div>
    <div class="list-group-item">
        Item 3
    </div>
</div>
CONTENTEND;
?>