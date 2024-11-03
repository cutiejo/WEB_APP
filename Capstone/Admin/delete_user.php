<?php
include 'db.php';
include 'auth.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: users.php');
    exit;
}

$id = $_GET['id'];
$conn->query("DELETE FROM users WHERE id = $id");
header('Location: users.php');
exit;
?>