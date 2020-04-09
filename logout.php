<?php

    include('./config.php');
    // Start the session
    session_start();
    session_unset();
    session_destroy();

    header('Location: ' . $ROOT_DIR . '/');

?>