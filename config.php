<?php

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    ini_set('session.cookie_lifetime', 60 * 60 * 24 * 100);
    ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 100);
    error_reporting(E_ALL);

    $ROOT_DIR = "";

    // Database
    $dbhost = "localhost";
    $dbuser = "root";
    $dbpass = "root";
    $db = "dqb";

?>