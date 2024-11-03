<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "school_rfid_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>



