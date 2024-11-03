<?php
include '../db.php';

// Debugging: Display all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure the content type is JSON
header('Content-Type: application/json');

// Query to fetch archived users
$sql = "SELECT id, full_name, email, role, archived_at FROM users WHERE archived = 1";
$result = $conn->query($sql);

$archived_users = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $archived_users[] = [
            'id' => $row['id'],
            'full_name' => $row['full_name'],
            'email' => $row['email'],
            'role' => $row['role'],
            'archived_at' => $row['archived_at']
        ];
    }
}

// Return JSON response
echo json_encode($archived_users);

$conn->close();
?>
