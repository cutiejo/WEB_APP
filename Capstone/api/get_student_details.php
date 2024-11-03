<?php
include '../db.php';

// Assume we're getting the user_id from the request (e.g., through RFID or session)
$user_id = $_GET['user_id'] ?? 1; // Example user ID

$query = "SELECT full_name AS name, lrn, grade_level, section, profile_picture FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$studentDetails = $result->fetch_assoc();

// Return data as JSON
echo json_encode($studentDetails);
?>