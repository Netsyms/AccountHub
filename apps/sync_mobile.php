<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

dieifnotloggedin();

use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;

if (MOBILE_ENABLED) {

    $APPS["sync_mobile"]["title"] = lang("sync mobile", false);
    $APPS["sync_mobile"]["icon"] = "mobile";

    if (!is_empty($_GET['delsynccode'])) {
        if ($database->has("mobile_codes", ["AND" => ["uid" => $_SESSION['uid'], "codeid" => $_GET['delsynccode']]])) {
            $database->delete("mobile_codes", ["AND" => ["uid" => $_SESSION['uid'], "codeid" => $_GET['delsynccode']]]);
        }
    }

    if ($_GET['mobilecode'] == "generate") {
        if (!is_empty($_GET['showsynccode']) && $database->has("mobile_codes", ["AND" => ["uid" => $_SESSION['uid'], "codeid" => $_GET['showsynccode']]])) {
            $code = $database->get("mobile_codes", 'code', ["AND" => ["uid" => $_SESSION['uid'], "codeid" => $_GET['showsynccode']]]);
        } else {
            $code = strtoupper(substr(md5(mt_rand() . uniqid("", true)), 0, 20));
            $database->insert('mobile_codes', ['uid' => $_SESSION['uid'], 'code' => $code]);
        }
        if (strpos(URL, "http") !== FALSE) {
            $url = URL . "mobile/index.php";
        } else {
            $url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . (($_SERVER['SERVER_PORT'] != 80 && $_SERVER['SERVER_PORT'] != 443) ? ":" . $_SERVER['SERVER_PORT'] : "") . URL . "mobile/index.php";
        }
        $encodedurl = str_replace("/", "\\", $url);
        $codeuri = "bizsync://" . $encodedurl . "/" . $_SESSION['username'] . "/" . $code;
        $qrCode = new QrCode($codeuri);
        $qrCode->setWriterByName('svg');
        $qrCode->setSize(550);
        $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH);
        $qrcode = $qrCode->writeDataUri();
        $chunk_code = trim(chunk_split($code, 5, ' '));
        $lang_done = lang("done adding sync code", false);
        $APPS["sync_mobile"]["content"] = '<div class="alert alert-info"><i class="fa fa-info-circle"></i> '
                . lang("scan sync qrcode", false)
                . '</div>'
                . <<<END
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
<div class="panel panel-default margintop-15px">
<div class="panel-body">
END
                . "<b>" . lang("manual setup", false) . "</b><br /><label>" . lang("username", false) . ":</label>"
                . '<div class="well well-sm mono-chunk">' . $_SESSION['username'] . '</div>'
                . "<label>" . lang("sync key", false) . "</label>"
                . <<<END
<div class="well well-sm mono-chunk">$chunk_code</div>
END
                . "<label>" . lang("url", false) . "</label>"
                . <<<END
<div class="well well-sm mono-chunk">$url</div>
</div>
</div>
<a class="btn btn-success btn-sm btn-block" href="home.php?page=sync">$lang_done</a>
END;
    } else {
        $activecodes = $database->select("mobile_codes", ["codeid", "code"], ["uid" => $_SESSION['uid']]);
        $content = '<div class="alert alert-info"><i class="fa fa-info-circle"></i> ' . lang("sync explained", false) . '</div>'
                . '<a class="btn btn-success btn-sm btn-block" href="home.php?page=sync&mobilecode=generate">'
                . lang("generate sync", false) . '</a>';
        $content .= "<br /><b>" . lang("active sync codes", false) . ":</b><br />";
        $content .= "<div class='list-group'>";
        if (count($activecodes) > 0) {
            foreach ($activecodes as $c) {
                $content .= "<div class='list-group-item mobilekey'><span id=\"mobilecode\">" . trim(chunk_split($c['code'], 5, ' ')) . "</span> <span class='tinybuttons'><a class='btn btn-primary btn-sm' href='home.php?page=sync&mobilecode=generate&showsynccode=" . $c['codeid'] . "'><i class='fa fa-qrcode'></i></a> <a class='btn btn-danger btn-sm' href='home.php?page=sync&delsynccode=" . $c['codeid'] . "'><i class='fa fa-trash'></i></a></span></div>";
            }
        } else {
            $content .= "<div class='list-group-item'>" . lang("no active codes", false) . "</div>";
        }
        $content .= "</div>";
        $content .= <<<END
            <style nonce="$SECURE_NONCE">
                .mobilekey {
                    display: flex;
                    flex-wrap: wrap;
                    justify-content: space-between;
                }
                .mobilekey #mobilecode {
                    font-family: Ubuntu Mono,monospace;
                    flex-shrink: 0;
                }
            </style>
END;
        $APPS["sync_mobile"]["content"] = $content;
    }
}