<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
if(!isset($_SESSION)) {
    session_start();
}

// SETTINGS
// Config file directory - Directory, where config file must be
define("CONFIG_FILE_DIRECTORY", "../");

// Config file name - Output file with config parameters (database, username etc.)
define("CONFIG_FILE_NAME", "config.php");

// According to directory hierarchy (you may add/remove "../" before CONFIG_FILE_DIRECTORY)
define("CONFIG_FILE_PATH", CONFIG_FILE_DIRECTORY . CONFIG_FILE_NAME);

// Config file name - config template file name
define("CONFIG_FILE_TEMPLATE", "config.tpl");

if (file_exists(CONFIG_FILE_PATH)) {
    echo '<meta http-equiv="refresh" content="0; url=../" />';
    exit;
}

function head()
{
    $current_page = substr($_SERVER["SCRIPT_NAME"], strrpos($_SERVER["SCRIPT_NAME"], "/") + 1);  

    if($current_page == 'settings.php'){
        $page = 2; 
    } elseif ($current_page == 'done.php') {
        $page = 3;
    } else {
        $page = 1;
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>phpBlog - Installation Wizard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../assets/img/favicon.png">
    <meta charset="utf-8">
    <META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
    <style>
        .checklist {
            list-style-type: none;
            padding-left: 0;
        }
        .checklist li {
            padding: 5px 0;
            font-size: 1.1rem;
        }
        .checklist .fas {
            margin-right: 10px;
        }
        #test-result .alert {
            margin-bottom: 0;
            padding: 10px 15px;
        }
        #loader-overlay {
            display: none; /* Caché par défaut */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.85); /* Fond blanc semi-transparent */
            z-index: 9999;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            gap: 15px;
            font-size: 1.5rem;
        }        
    </style>
</head>

<body>

    <div class="container">
        <div class="bg-light rounded mt-4">
            <div class="row d-flex justify-content-center">
                <div class="col-lg-10">
                    <br /><center><h3><i class="fas fa-file-alt"></i> phpBlog - Installation Wizard</h3></center><br />
                        <div class="jumbotron">
                            <ul class="nav nav-tabs nav-fill">
                                <li class="nav-item">
                                    <a class="nav-link <?php if($page == 1) { echo 'active'; } ?>"><i class="fas fa-database"></i> Database Settings</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php if($page == 2) { echo 'active'; } ?>"><i class="fas fa-user"></i> Administrator Account</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php if($page == 3) { echo 'active'; } ?>"><i class="fas fa-check-square"></i> Completed</a>
                                </li>
                            </ul><br />
                            <div class="tab-content" id="TabContent">
<?php
}

function footer()
{
?>
                            </div><br />
                        </div>
                </div>
            </div>
        </div>
    </div>

    <div id="loader-overlay">
        <i class="fas fa-spinner fa-spin fa-2x"></i>
        <span>Installation in progress...</span>
    </div>
    </body>
</html>
<?php
}
?>