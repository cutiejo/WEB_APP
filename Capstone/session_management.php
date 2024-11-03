<?php
session_start();

// Custom timeout duration (30 minutes)
$timeout_duration = 1800;

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: ../Login/login.php");
    exit();
}

$_SESSION['LAST_ACTIVITY'] = time(); // Update last activity time
?>
