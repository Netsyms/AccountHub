<?php

dieifnotloggedin();

use Endroid\QrCode\QrCode;

if (MOBILE_ENABLED) {
    addMultiLangStrings(["en_us" => [
            "sync mobile" => "Sync Mobile App",
            "scan sync qrcode" => "Scan this code with the mobile app or enter the code manually.",
            "sync explained" => "Access your account and apps on the go.  Use a sync code to securely connect your phone or tablet to AccountHub with the Netsyms Business mobile app.",
            "generate sync" => "Create new sync code",
            "active sync codes" => "Active codes",
            "no active codes" => "No active codes.",
            "done adding sync code" => "Done adding code",
            "manual setup" => "Manual Setup:",
            "sync key" => "Sync key:",
            "url" => "URL:",
        ]
    ]);

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
        $qrCode->setSize(200);
        $qrCode->setErrorCorrection("H");
        $qrcode = $qrCode->getDataUri();
        $chunk_code = trim(chunk_split($code, 5, ' '));
        $lang_done = lang("done adding sync code", false);
        $APPS["sync_mobile"]["content"] = '<div class="alert alert-info"><i class="fa fa-info-circle"></i> '
                . lang("scan sync qrcode", false)
                . '</div>'
                . <<<END
<img src="$qrcode" class="img-responsive qrcode" />
<div class="panel panel-default" style="margin-top: 15px;">
<div class="panel-body">
END
                . "<b>" . lang("manual setup", false) . "</b><br /><label>" . lang("username", false) . ":</label>"
                . '<div class="well well-sm" style="text-align: center; font-size: 110%; font-family: monospace;">' . $_SESSION['username'] . '</div>'
                . "<label>" . lang("sync key", false) . "</label>"
                . <<<END
<div class="well well-sm" style="text-align: center; font-size: 110%; font-family: monospace;">$chunk_code</div>
END
                . "<label>" . lang("url", false) . "</label>"
                . <<<END
<div class="well well-sm" style="text-align: center; font-size: 110%; font-family: monospace;">$url</div>
</div>
</div>
<a class="btn btn-success btn-sm btn-block" href="home.php?page=security">$lang_done</a>
END;
    } else {
        $activecodes = $database->select("mobile_codes", ["codeid", "code"], ["uid" => $_SESSION['uid']]);
        $content = '<div class="alert alert-info"><i class="fa fa-info-circle"></i> ' . lang("sync explained", false) . '</div>'
                . '<a class="btn btn-success btn-sm btn-block" href="home.php?page=security&mobilecode=generate">'
                . lang("generate sync", false) . '</a>';
        $content .= "<br /><b>" . lang("active sync codes", false) . ":</b><br />";
        $content .= "<div class='list-group'>";
        if (count($activecodes) > 0) {
            foreach ($activecodes as $c) {
                $content .= "<div class='list-group-item mobilekey'><span style='font-family: Ubuntu Mono,monospace; flex-shrink: 0'>" . trim(chunk_split($c['code'], 5, ' ')) . "</span> <span class='tinybuttons'><a class='btn btn-primary btn-sm' href='home.php?page=security&mobilecode=generate&showsynccode=" . $c['codeid'] . "'><i class='fa fa-qrcode'></i></a> <a class='btn btn-danger btn-sm' href='home.php?page=security&delsynccode=" . $c['codeid'] . "'><i class='fa fa-trash'></i></a></span></div>";
            }
        } else {
            $content .= "<div class='list-group-item'>" . lang("no active codes", false) . "</div>";
        }
        $content .= "</div>";
        $content .= <<<END
            <style>
                .mobilekey {
                    display: flex;
                    flex-wrap: wrap;
                    justify-content: space-between;
                }
            </style>
END;
        $APPS["sync_mobile"]["content"] = $content;
    }
}