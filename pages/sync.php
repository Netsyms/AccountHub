<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;

if (!empty($_GET['delsynccode'])) {
    if ($database->has("mobile_codes", ["AND" => ["uid" => $_SESSION['uid'], "codeid" => $_GET['delsynccode']]])) {
        $database->delete("mobile_codes", ["AND" => ["uid" => $_SESSION['uid'], "codeid" => $_GET['delsynccode']]]);
    }
}
?>
<div class="row justify-content-center">
    <div class="col-sm-10 col-md-6 col-lg-4 col-xl-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-mobile-alt"></i> <?php $Strings->get("sync mobile"); ?></h5>
                <hr />
                <?php
                if (!empty($_GET['mobilecode']) && $_GET['mobilecode'] == "generate") {
                    if (!empty($_GET['showsynccode']) && $database->has("mobile_codes", ["AND" => ["uid" => $_SESSION['uid'], "codeid" => $_GET['showsynccode']]])) {
                        $code = $database->get("mobile_codes", 'code', ["AND" => ["uid" => $_SESSION['uid'], "codeid" => $_GET['showsynccode']]]);
                    } else {
                        $code = strtoupper(substr(md5(mt_rand() . uniqid("", true)), 0, 20));
                        $desc = htmlspecialchars($_POST['desc']);
                        $database->insert('mobile_codes', ['uid' => $_SESSION['uid'], 'code' => $code, 'description' => $desc]);
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
                    $lang_done = $Strings->get("done adding sync code", false);
                    ?>
                    <p class="card-text"><?php $Strings->get("scan sync qrcode"); ?></p>
                </div>
                <img src="<?php echo $qrcode; ?>" class="card-img px-4" />
                <div class="card-body">
                    <a class="btn btn-success btn-block" href="app.php?page=sync"><?php $Strings->get("done adding sync code"); ?></a>
                </div>
                <div class="list-group list-group-flush">
                    <div class="list-group-item">
                        <b><?php $Strings->get("manual setup"); ?></b>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-baseline">
                        <div><?php $Strings->get("username"); ?>:</div>
                        <div class="text-monospace text-right"><?php echo $_SESSION['username']; ?></div>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-baseline">
                        <div><?php $Strings->get("sync key"); ?></div>
                        <div class="text-monospace text-right"><?php echo $chunk_code; ?></div>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-baseline">
                        <div><?php $Strings->get("url"); ?></div>
                        <div class="text-monospace text-right"><?php echo $url; ?></div>
                    </div>
                </div>
                <?php
            } else {
                $activecodes = $database->select("mobile_codes", ["codeid", "code", "description"], ["uid" => $_SESSION['uid']]);
                ?>
                <p class="card-text">
                    <?php $Strings->get("sync explained"); ?>
                </p>
                <form action="app.php?page=sync&mobilecode=generate" method="POST">
                    <input type="text" name="desc" class="form-control" placeholder="<?php $Strings->get("sync code name"); ?>" required />
                    <button class="btn btn-success btn-block mt-2" type="submit">
                        <?php $Strings->get("generate sync"); ?>
                    </button>
                </form>
            </div>
            <div class="list-group list-group-flush">
                <div class="list-group-item">
                    <b><?php $Strings->get("active sync codes"); ?></b>
                </div>
                <?php
                if (count($activecodes) > 0) {
                    foreach ($activecodes as $c) {
                        ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-monospace">
                                    <?php echo trim(chunk_split($c['code'], 5, ' ')); ?>
                                </div>
                                <div class="text-muted">
                                    <i class="fas fa-mobile-alt"></i> <?php echo $c['description']; ?>
                                </div>
                            </div>
                            <div>
                                <a class="btn btn-primary btn-sm m-1" href="app.php?page=sync&mobilecode=generate&showsynccode=<?php echo $c['codeid']; ?>">
                                    <i class="fas fa-qrcode"></i>
                                </a>
                                <a class="btn btn-danger btn-sm m-1" href="app.php?page=sync&delsynccode=<?php echo $c['codeid']; ?>">
                                    <i class='fas fa-trash'></i>
                                </a>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div class="list-group-item">
                        <?php $Strings->get("no active codes"); ?>
                    </div>
                    <?php
                }
                ?>
            </div>
            <?php
        }
        ?>
    </div>
</div>

</div>