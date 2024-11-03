<?php
$host = 'localhost';
$db = 'school_rfid_db';
$user = 'root';
$pass = '';


$conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>