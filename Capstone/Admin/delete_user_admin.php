<?php
include '../db.php';

if (isset($_POST['ids'])) {
    $userIds = $_POST['ids'];

    // Convert IDs array to a comma-separated string
    $userIdsStr = implode(',', array_map('intval', $userIds));

    // Prepare the query to delete the selected users
    $query = "DELETE FROM users WHERE id IN ($userIdsStr)";
    if ($conn->query($query)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete users']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No user IDs provided']);
}

$conn->close();
