<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user']);
}

function setUserSession($user) {
    $_SESSION['user'] = $user;
}

function logout() {
    session_destroy();
}
?>
