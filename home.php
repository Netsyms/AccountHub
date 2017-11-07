<?php
require_once __DIR__ . "/required.php";

if ($_SESSION['loggedin'] != true) {
    header('Location: index.php');
    die("Session expired.  Log in again to continue.");
} else if (is_empty($_SESSION['password'])) {
    header('Location: index.php');
    die("You need to log in again.");
}

require_once __DIR__ . "/pages.php";

$pageid = "home";
if (!is_empty($_GET['page'])) {
    if (array_key_exists($_GET['page'], PAGES)) {
        $pageid = $_GET['page'];
    } else {
        $pageid = "404";
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title><?php echo SITE_TITLE; ?></title>

        <link href="static/css/bootstrap.min.css" rel="stylesheet">
        <link href="static/css/font-awesome.min.css" rel="stylesheet">
        <link href="static/css/material-color/material-color.min.css" rel="stylesheet">
        <link href="static/css/app.css" rel="stylesheet">
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 col-sm-offset-3 col-md-offset-4 col-lg-offset-4">
                    <?php
                    if ((SHOW_ICON == "both" || SHOW_ICON == "app") && ICON_POSITION != "menu") {
                        if (MENU_BAR_STYLE != "fixed") {
                            ?>
                            <img class="img-responsive banner-image" src="static/img/logo.svg" />
                            <?php
                        }
                    }
                    ?>
                </div>
            </div>
            <nav class="navbar navbar-default navbar-orange navbar-<?php echo MENU_BAR_STYLE; ?>-top">
                <div class="container-fluid">
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                        <?php
                        if (SHOW_ICON == "both" || SHOW_ICON == "app") {
                            if (MENU_BAR_STYLE == "fixed" || ICON_POSITION == "menu") {
                                $src = "static/img/logo.svg";
                                if ($pageid != "home") {
                                    $src = "static/img/up-arrow-black.png";
                                }
                                ?>
                                <a class="navbar-brand" href="home.php">
                                    <img style="height: 35px; padding-bottom: 12px; padding-left: 5px;" src="<?php echo $src; ?>" />
                                </a>
                                <?php
                            }
                        }
                        ?>
                        <a class="navbar-brand" href="home.php">
                            <?php
                            echo SITE_TITLE;
                            ?>
                        </a>
                    </div>

                    <div class="collapse navbar-collapse" id="navbar-collapse">
                        <ul class="nav navbar-nav">
                            <?php
                            foreach (PAGES as $id => $pg) {
                                if ($pg['navbar'] === TRUE) {
                                    if ($pageid == $id) {
                                        ?>
                                        <li class="active">
                                            <?php
                                        } else {
                                            ?>
                                        <li>
                                        <?php } ?>
                                        <a href="home.php?page=<?php echo $id; ?>">
                                            <?php
                                            if (isset($pg['icon'])) {
                                                ?>
                                                <i class="fa fa-<?php echo $pg['icon']; ?> fa-fw"></i> 
                                            <?php } ?>
                                            <?php lang($pg['title']) ?>
                                        </a>
                                    </li>
                                    <?php
                                }
                            }
                            ?>
                        </ul>
                        <ul class="nav navbar-nav navbar-right">
                            <li><a href="home.php"><i class="fa fa-user fa-fw"></i> <?php echo $_SESSION['realname'] ?></a></li>
                            <li><a href="action.php?action=signout"><i class="fa fa-sign-out fa-fw"></i> <?php lang("sign out") ?></a></li>
                        </ul>
                    </div>
            </nav>
            <?php
            if (MENU_BAR_STYLE == "fixed") {
                ?>
                <div style="height: 75px;"></div>
                <?php
            }
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

            <?php
            // Alert messages
            if (!is_empty($_GET['msg']) && array_key_exists($_GET['msg'], MESSAGES)) {
                // optional string generation argument
                if (is_empty($_GET['arg'])) {
                    $alertmsg = lang(MESSAGES[$_GET['msg']]['string'], false);
                } else {
                    $alertmsg = lang2(MESSAGES[$_GET['msg']]['string'], ["arg" => $_GET['arg']], false);
                }
                $alerttype = MESSAGES[$_GET['msg']]['type'];
                $alerticon = "square-o";
                switch (MESSAGES[$_GET['msg']]['type']) {
                    case "danger":
                        $alerticon = "times";
                        break;
                    case "warning":
                        $alerticon = "exclamation-triangle";
                        break;
                    case "info":
                        $alerticon = "info-circle";
                        break;
                    case "success":
                        $alerticon = "check";
                        break;
                }
                echo <<<END
            <div class="row">
                <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 col-sm-offset-3 col-md-offset-4 col-lg-offset-4">
                    <div class="alert alert-dismissible alert-$alerttype">
                        <button type="button" class="close">&times;</button>
                        <i class="fa fa-$alerticon"></i> $alertmsg
                    </div>
                </div>
            </div>
END;
            }
            ?>
            <div class="row widget-box">
                <?php
                // Center the widgets horizontally on the screen
                $appcount = 0;
                foreach (APPS[$pageid] as $app) {
                    if (file_exists(__DIR__ . "/apps/" . $app . ".php")) {
                        include_once __DIR__ . "/apps/" . $app . ".php";
                        if (isset($APPS[$app])) {
                            $appcount++;
                        }
                    }
                }
                if ($appcount == 1) {
                    ?>
                    <div class="hidden-xs col-sm-3 col-md-4 col-lg-4">
                        <!-- Empty placeholder column for nice center-align -->
                    </div>
                    <?php
                } else if ($appcount == 2) {
                    ?>
                    <div class="hidden-xs hidden-sm col-md-2 col-lg-2">
                        <!-- Empty placeholder column for nice center-align -->
                    </div>
                    <?php
                }

                // Load app widgets
                foreach (APPS[$pageid] as $app) {
                    if (file_exists(__DIR__ . "/apps/" . $app . ".php")) {
                        include_once __DIR__ . "/apps/" . $app . ".php";
                        if (!isset($APPS[$app])) {
                            continue;
                        }
                        $apptitle = ($APPS[$app]['i18n'] === TRUE ? lang($APPS[$app]['title'], false) : $APPS[$app]['title']);
                        $appicon = (is_empty($APPS[$app]['icon']) ? "" : "fa fa-fw fa-" . $APPS[$app]['icon']);
                        $apptype = (is_empty($APPS[$app]['type']) ? "default" : $APPS[$app]['type']);
                        $appcontent = $APPS[$app]['content'];
                        echo <<<END
                        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
                            <div class="panel panel-$apptype apppanel">
                                <div class="panel-heading">
                                    <h3 class="panel-title"><i class="$appicon"></i> $apptitle </h3>
                                </div>
                                <div class="panel-body">
                                    $appcontent
                                </div>
                            </div>
                        </div>
END;
                    }
                }
                ?>
            </div>
            <div class="footer">
                <?php echo LICENSE_TEXT; ?><br />
                Copyright &copy; <?php echo date('Y'); ?> <?php echo COPYRIGHT_NAME; ?>
            </div>
        </div>
        <script src="static/js/jquery-3.2.1.min.js"></script>
        <script src="static/js/bootstrap.min.js"></script>
        <script src="static/js/app.js"></script>
    </body>
</html>