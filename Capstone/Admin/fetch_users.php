<?php
include '../db.php'; // Include your database connection

header('Content-Type: application/json'); // Set header for JSON

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Fetch all users (not just one)
$sql = "SELECT id, full_name, email, role, created_at FROM users WHERE archived = 0"; // Adjust if necessary
$result = $conn->query($sql);

$users = []; // Initialize array for user data

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row; // Append each user to the array
    }
}

// Return the data in the correct DataTables format
echo json_encode(['data' => $users]);

$conn->close(); // Close the connection
?>