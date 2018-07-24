<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
?>

<div class="mobile-app-hide d-flex justify-content-center flex-wrap">
    <?php
    foreach (EXTERNAL_APPS as $a) {
        ?>
        <div class="app-dock-item m-2">
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

<div class="mobile-app-hide row mt-4">
    <?php
    foreach (EXTERNAL_APPS as $a) {
        if (!isset($a['card'])) {
            continue;
        }
        ?>
        <div class="col-12 col-sm-6 col-md-4 mb-4">
            <div class="card bg-<?php echo $a['card']['color']; ?> h-100">
                <div class="card-body align-middle">
                    <a href="<?php echo $a['url']; ?>" class="row align-items-center h-100 text-<?php echo (empty($a['card']['text']) ? "light" : $a['card']['text']) ?>">
                        <div class="col-4 col-sm-4">
                            <img class="img-fluid" src="<?php
                            if (strpos($a['icon'], "http") !== 0) {
                                echo $a['url'] . $a['icon'];
                            } else {
                                echo $a['icon'];
                            }
                            ?>"/>
                        </div>
                        <div class="col-12 col-sm-8">
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
</div>