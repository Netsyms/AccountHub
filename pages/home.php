<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
?>

<div class="d-flex justify-content-center flex-wrap">
    <?php
    foreach (EXTERNAL_APPS as $a) {
        ?>
        <div class="app-dock-item m-2 mobile-app-hide">
            <p class="mb-0">
                <a href="<?php echo $a['url']; ?>">
                    <img class="img-responsive app-icon" src="<?php
                    if (strpos($a['icon'], "http") !== 0) {
                        echo $a['url'] . $a['icon'];
                    } else {
                        echo $a['icon'];
                    }
                    ?>"/>
                    <span class="d-block text-center"><?php echo $a['title']; ?></span>
                </a>
            </p>
        </div>
        <?php
    }
    ?>
</div>

<div class="row mt-2">
    <?php
    foreach (EXTERNAL_APPS as $a) {
        if (!isset($a['card'])) {
            continue;
        }
        ?>
        <div class="col-12 col-sm-6 col-md-4 mb-4 mobile-app-hide">
            <div class="card bg-<?php echo $a['card']['color']; ?> h-100">
                <div class="card-body align-middle">
                    <a href="<?php echo $a['url']; ?>" class="row align-items-center h-100 text-<?php echo (empty($a['card']['text']) ? "light" : $a['card']['text']) ?>">
                        <div class="col-4">
                            <img class="img-fluid" src="<?php
                            if (strpos($a['icon'], "http") !== 0) {
                                echo $a['url'] . $a['icon'];
                            } else {
                                echo $a['icon'];
                            }
                            ?>"/>
                        </div>
                        <div class="col-8">
                            <span class="h5 font-weight-normal"><?php echo $a['title']; ?></span><br />
                            <?php $Strings->get($a['card']['string']); ?>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
    <div class="col-12 col-sm-6 col-md-4 mb-4">
        <div class="card bg-orange h-100">
            <div class="card-body align-middle">
                <a href="./app.php?page=security" class="row align-items-center h-100 text-dark">
                    <div class="col-4 text-center">
                        <i class="fas fa-lock fa-4x"></i>
                    </div>
                    <div class="col-8">
                        <span class="h5 font-weight-normal"><?php $Strings->get("account security"); ?></span><br />
                        <?php $Strings->get("Change password, setup 2-factor, and change Station PIN"); ?>
                    </div>
                </a>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 mb-4">
        <div class="card bg-orange h-100">
            <div class="card-body align-middle">
                <a href="./app.php?page=sync" class="row align-items-center h-100 text-dark">
                    <div class="col-4 text-center">
                        <i class="fas fa-sync fa-4x"></i>
                    </div>
                    <div class="col-8">
                        <span class="h5 font-weight-normal"><?php $Strings->get("sync"); ?></span><br />
                        <?php $Strings->get("Connect mobile devices to AccountHub"); ?>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<h3 class="font-weight-normal mt-4" id="notifications"><i class="fas fa-bell"></i> <?php $Strings->get("Notifications"); ?></h3>
<div class="row">
    <?php
    $notifications = Notifications::get(User::byUsername($_SESSION['username']));
    foreach ($notifications as $n) {
        ?>
        <div class="col-12 col-sm-6 col-md-4 col-xl-3">
            <div class="card mb-4">
                <div class="card-body <?php echo ($n['seen'] ? "text-muted" : "font-weight-bold"); ?>">
                    <div class="d-flex flex-wrap justify-content-between">
                        <h5 class="card-title"><?php echo $n['title']; ?></h5>
                        <div class="d-flex flex-wrap">
                            <form action="action.php" method="POST" class="mr-2">
                                <input type="hidden" name="source" value="home" />
                                <input type="hidden" name="id" value="<?php echo $n['id']; ?>" />
                                <button type="submit" class="btn btn-sm btn-primary" name="action" value="readnotification" title="<?php $Strings->get("Mark as read"); ?>">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </form>
                            <form action="action.php" method="POST">
                                <input type="hidden" name="source" value="home" />
                                <input type="hidden" name="id" value="<?php echo $n['id']; ?>" />
                                <button type="submit" class="btn btn-sm btn-danger" name="action" value="deletenotification" title="<?php $Strings->get("Delete"); ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="card-text"><?php echo $n['content']; ?></div>
                </div>
                <div class="card-footer">
                    <div class="card-text">
                        <i class="fas fa-clock"></i>
                        <?php
                        $ts = strtotime($n['timestamp']);
                        if (time() - $ts < 60 * 60 * 12) {
                            echo date(TIME_FORMAT, $ts);
                        } else {
                            echo date(DATETIME_FORMAT, $ts);
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
</div>