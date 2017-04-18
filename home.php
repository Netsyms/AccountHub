<?php
require_once __DIR__ . "/required.php";

if ($_SESSION['loggedin'] != true) {
    header('Location: index.php');
    die("Session expired.  Log in again to continue.");
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
        <meta name="viewport" contgreent="width=device-width, initial-scale=1">

        <title><?php echo SITE_TITLE; ?></title>

        <link href="static/css/bootstrap.min.css" rel="stylesheet">
        <link href="static/css/font-awesome.min.css" rel="stylesheet">
        <link href="static/css/app.css" rel="stylesheet">
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 col-sm-offset-3 col-md-offset-4 col-lg-offset-4">
                    <img class="img-responsive banner-image" src="static/img/banner.png" />
                </div>
            </div>
            <nav class="navbar navbar-inverse">
                <div class="container-fluid">
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                        <?php
                        if (PAGES[$pageid]['title'] == "{DEFAULT}") {
                            ?>
                            <span class="navbar-brand">
                                <?php
                                lang2("welcome user", ["user" => $_SESSION['realname']]);
                                ?>
                            </span>
                            <?php
                        } else {
                            ?>
                            <a class="navbar-brand" href="home.php?page=home">
                                <?php
                                // add breadcrumb thing
                                lang("home");
                                echo " <i class=\"fa fa-caret-right\"></i> ";
                                lang(PAGES[$pageid]['title']);
                                ?>
                            </a>
                            <?php
                        }
                        ?>
                    </div>

                    <div class="collapse navbar-collapse" id="navbar-collapse">
                        <ul class="nav navbar-nav">
                        </ul>
                        <ul class="nav navbar-nav navbar-right">
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-gears fa-fw"></i> <?php lang("options") ?> <span class="caret"></span></a>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="home.php?page=security"><i class="fa fa-lock fa-fw"></i> <?php lang("account security") ?></a></li>
                                    <li class="divider"></li>
                                    <li><a href="action.php?action=signout"><i class="fa fa-sign-out fa-fw"></i> <?php lang("sign out") ?></a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
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
            <div class="row">
                <?php
                // Center the widgets horizontally on the screen
                $appcount = count(APPS[$pageid]);
                if ($appcount == 1) {
                    ?>
                    <div class="hidden-xs col-sm-3 col-md-4 col-lg-4">
                        <!-- Placeholder column for nice center-align -->
                    </div>
                    <?php
                } else if ($appcount == 2) {
                    ?>
                    <div class="hidden-xs hidden-sm col-md-2 col-lg-2">
                        <!-- Placeholder column for nice center-align -->
                    </div>
                    <?php
                }

                foreach (APPS[$pageid] as $app) {
                    if (file_exists(__DIR__ . "/apps/" . $app . ".php")) {
                        include_once __DIR__ . "/apps/" . $app . ".php";
                        $apptitle = $APPS[$app]['title'];
                        $appicon = (is_empty($APPS[$app]['icon']) ? "" : "fa fa-fw fa-" . $APPS[$app]['icon']);
                        $apptype = (is_empty($APPS[$app]['type']) ? "default" : $APPS[$app]['type']);
                        $appcontent = $APPS[$app]['content'];
                        echo <<<END
                        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
                            <div class="panel panel-$apptype">
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