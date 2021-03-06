<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

use OTPHP\Factory;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;

$user = new User($_SESSION['uid']);

if (!empty($_GET['delpass'])) {
    if ($database->has("apppasswords", ["AND" => ["uid" => $_SESSION['uid'], "passid" => $_GET['delpass']]])) {
        $database->delete("apppasswords", ["AND" => ["uid" => $_SESSION['uid'], "passid" => $_GET['delpass']]]);
    }
}
?>
<div class="row justify-content-center">

    <div class="col-sm-6 col-lg-4">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-key"></i> <?php $Strings->get("change password"); ?></h5>
                <hr />
                <form action="action.php" method="POST">
                    <input type="password" class="form-control" name="oldpass" placeholder="<?php $Strings->get("current password"); ?>" />
                    <input type="password" class="form-control" name="newpass" placeholder="<?php $Strings->get("new password"); ?>" />
                    <input type="password" class="form-control" name="conpass" placeholder="<?php $Strings->get("confirm password"); ?>" />
                    <input type="hidden" name="action" value="chpasswd" />
                    <input type="hidden" name="source" value="security" />
                    <br />
                    <button type="submit" class="btn btn-success btn-block"><?php $Strings->get("change password"); ?></button>
                </form>
            </div>
        </div>
    </div>

    <?php
    if ($SETTINGS['station_kiosk']) {
        ?>
        <div class="col-sm-6 col-lg-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-th"></i> <?php $Strings->get("change pin"); ?></h5>
                    <hr />
                    <?php $Strings->get("pin explanation"); ?>
                    <hr />
                    <form action="action.php" method="POST">
                        <input type="password" class="form-control" name="newpin" placeholder="<?php $Strings->get("new pin"); ?>" maxlength="8" pattern="[0-9]*" inputmode="numeric" />
                        <input type="password" class="form-control" name="conpin" placeholder="<?php $Strings->get("confirm pin"); ?>" maxlength="8" pattern="[0-9]*" inputmode="numeric" />
                        <input type="hidden" name="action" value="chpin" />
                        <input type="hidden" name="source" value="security" />
                        <br />
                        <button type="submit" class="btn btn-success btn-block"><?php $Strings->get("change pin"); ?></button>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }
    ?>

    <div class="col-sm-6 col-lg-4">
        <div class="card mb-4">
            <div class="card-body pb-0">
                <h5 class="card-title"><i class="fas fa-mobile-alt"></i> <?php $Strings->get("setup 2fa"); ?></h5>
                <hr />
            </div>
            <?php
            if ($user->has2fa()) {
                ?>
                <div class="card-body pt-0">
                    <?php $Strings->get("2fa active") ?>
                    <hr />
                    <form action="action.php" method="POST">
                        <input type="hidden" name="action" value="rm2fa" />
                        <input type="hidden" name="source" value="security" />
                        <button type="submit" class="btn btn-info btn-block"><?php $Strings->get("remove 2fa") ?></button>
                    </form>
                </div>
                <?php
            } else if (!empty($_GET['2fa']) && $_GET['2fa'] == "generate") {
                $codeuri = $user->generate2fa();
                $label = $SETTINGS['system_name'] . ":" . is_null($user->getEmail()) ? $user->getName() : $user->getEmail();
                $issuer = $SETTINGS['system_name'];
                $qrCode = new QrCode($codeuri);
                $qrCode->setWriterByName('svg');
                $qrCode->setSize(550);
                $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH());
                $qrcode = $qrCode->writeDataUri();
                $totp = Factory::loadFromProvisioningUri($codeuri);
                $codesecret = $totp->getSecret();
                $chunk_secret = trim(chunk_split($codesecret, 4, ' '));
                ?>
                <div class="card-body pt-0">
                    <div class="card-text">
                        <?php $Strings->get("scan 2fa qrcode") ?>
                    </div>
                </div>
                <img src="<?php echo $qrcode; ?>" class="card-img px-4" />
                <div class="card-body">
                    <form action="action.php" method="POST">
                        <input type="text" name="totpcode" class="form-control" placeholder="<?php $Strings->get("enter otp code"); ?>" minlength=6 maxlength=6 required />
                        <br />
                        <input type="hidden" name="action" value="add2fa" />
                        <input type="hidden" name="source" value="security" />
                        <input type="hidden" name="secret" value="<?php echo $codesecret; ?>" />
                        <button type="submit" class="btn btn-success btn-block">
                            <?php $Strings->get("confirm 2fa") ?>
                        </button>
                    </form>
                </div>
                <div class="list-group list-group-flush">
                    <div class="list-group-item">
                        <b><?php $Strings->get("manual setup"); ?></b>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-baseline">
                        <div><?php $Strings->get("secret key"); ?></div>
                        <div class="text-monospace text-right"><?php echo $chunk_secret; ?></div>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-baseline">
                        <div><?php $Strings->get("label"); ?></div>
                        <div class="text-monospace text-right"><?php echo $label; ?></div>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-baseline">
                        <div><?php $Strings->get("issuer"); ?></div>
                        <div class="text-monospace text-right"><?php echo $issuer; ?></div>
                    </div>
                </div>
                <?php
            } else {
                ?>
                <div class="card-body pt-0">
                    <?php $Strings->get("2fa explained"); ?>
                    <hr />
                    <a class="btn btn-success btn-block" href="app.php?page=security&2fa=generate">
                        <?php $Strings->get("enable 2fa"); ?>
                    </a>
                </div>
                <?php
            }
            ?>
        </div>
    </div>

    <div class="col-sm-10 col-md-6 col-lg-4 col-xl-4">
        <div class="card mb-4">
            <?php
            if (!empty($_GET['apppassword']) && $_GET['apppassword'] == "generate" && !empty($_POST['desc'])) {
                $code = strtoupper(substr(md5(mt_rand() . uniqid("", true)), 0, 20));
                $desc = htmlspecialchars($_POST['desc']);
                $chunk_code = str_replace(" ", "-", trim(chunk_split($code, 5, ' ')));
                $database->insert('apppasswords', ['uid' => $_SESSION['uid'], 'hash' => password_hash($chunk_code, PASSWORD_DEFAULT), 'description' => $desc]);
                ?>
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-shield-alt"></i> <?php $Strings->get("App Passwords"); ?></h5>
                    <hr />

                    <?php $Strings->build("app password setup instructions", ["app_name" => $desc]); ?>
                </div>
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between align-items-baseline">
                        <div><?php $Strings->get("username"); ?>:</div>
                        <div class="text-monospace text-right"><?php echo $_SESSION['username']; ?></div>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-baseline">
                        <div><?php $Strings->get("password"); ?></div>
                        <div class="text-monospace text-right"><?php echo $chunk_code; ?></div>
                    </div>
                </div>
                <div class="card-body">
                    <a class="btn btn-success btn-block" href="app.php?page=security"><?php $Strings->get("Done"); ?></a>
                </div>
                <?php
            } else {
                $activecodes = $database->select("apppasswords", ["passid", "description"], ["uid" => $_SESSION['uid']]);
                ?>
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-shield-alt"></i> <?php $Strings->get("App Passwords"); ?></h5>
                    <hr />
                    <p class="card-text">
                        <?php $Strings->build("app passwords explained", ["site_name" => $SETTINGS['site_title']]); ?>
                    </p>
                    <form action="app.php?page=security&apppassword=generate" method="POST">
                        <input type="text" name="desc" class="form-control" placeholder="<?php $Strings->get("App name"); ?>" required />
                        <button class="btn btn-success btn-block mt-2" type="submit">
                            <?php $Strings->get("Generate password"); ?>
                        </button>
                    </form>
                </div>
                <div class="list-group list-group-flush">
                    <div class="list-group-item">
                        <b><?php $Strings->get("App Passwords"); ?></b>
                    </div>
                    <?php
                    if (count($activecodes) > 0) {
                        foreach ($activecodes as $c) {
                            ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="">
                                        <?php echo $c['description']; ?>
                                    </div>
                                </div>
                                <div>
                                    <a class="btn btn-danger btn-sm m-1" href="app.php?page=security&delpass=<?php echo $c['passid']; ?>" data-toggle="tooltip" data-placement="bottom" title="<?php $Strings->get("Revoke password"); ?>">
                                        <i class='fas fa-trash'></i><noscript> <?php $Strings->get("Revoke password"); ?></noscript>
                                    </a>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        ?>
                        <div class="list-group-item">
                            <?php $Strings->get("You don't have any app passwords."); ?>
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