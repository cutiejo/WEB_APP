<?php
include 'db.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $rfid_uid = $_POST['rfid_uid'];

    // Assign RFID UID to the selected user (student or teacher)
    $stmt = $conn->prepare("UPDATE students SET rfid_uid = ? WHERE id = ?");
    $stmt->bind_param("si", $rfid_uid, $user_id);
    $stmt->execute();

    echo "RFID UID assigned successfully.";
}
?>