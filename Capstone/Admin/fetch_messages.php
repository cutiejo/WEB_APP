<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => false, 'message' => 'Unauthorized']);
    exit();
}

$receiver_id = $_GET['receiver_id'];
$response = [];

// Fetch messages for a specific conversation
$query = "SELECT * FROM messages WHERE sender_id = '$receiver_id' OR receiver_id = '$receiver_id' ORDER BY created_at ASC";
$result = mysqli_query($conn, $query);

if ($result) {
    $messages = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $messages[] = $row;
    }
    $response['status'] = true;
    $response['messages'] = $messages;
} else {
    $response['status'] = false;
    $response['message'] = 'Failed to fetch messages';
}

echo json_encode($response);
?>