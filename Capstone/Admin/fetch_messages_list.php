<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => false, 'message' => 'Unauthorized']);
    exit();
}

$response = [];

// Modify query to fetch actual last message and its timestamp
$query = "SELECT users.id, users.role, MAX(messages.created_at) AS last_message_time 
          FROM users 
          LEFT JOIN messages ON users.id = messages.sender_id OR users.id = messages.receiver_id
          GROUP BY users.id";
$result = mysqli_query($conn, $query);

if ($result) {
    $users = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Ensure that the timestamp is correctly formatted
        $row['last_message_time'] = $row['last_message_time'] ? date('Y-m-d H:i:s', strtotime($row['last_message_time'])) : null;
        $users[] = $row;
    }
    $response['status'] = true;
    $response['users'] = $users;
} else {
    $response['status'] = false;
    $response['message'] = 'Failed to fetch users';
}

echo json_encode($response);
