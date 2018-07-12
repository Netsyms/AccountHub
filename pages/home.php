<?php
/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
?>

<div class="app-dock-container mobile-app-hide">
    <div class="app-dock">
<?php
foreach (EXTERNAL_APPS as $a) {
    ?>
            <div class="app-dock-item">
                <p>
                    <a href="<?php echo $a['url']; ?>">
                        <img class="img-responsive app-icon" src="<?php
        if (strpos($a['icon'], "http") !== 0) {
            echo $a['url'] . $a['icon'];
        } else {
            echo $a['icon'];
        }
    ?>"/>
                        <span><?php echo $a['title']; ?></span>
                    </a>
                </p>
            </div>
    <?php
}
?>
    </div>
</div>