<?php
include '../db.php';

if (isset($_POST['ids'])) {
    $userIds = $_POST['ids'];

    // Convert IDs array to a comma-separated string
    $userIdsStr = implode(',', array_map('intval', $userIds));

    // Prepare the query to archive the selected users and set the archived_at timestamp
    $query = "UPDATE users SET archived = 1, archived_at = NOW() WHERE id IN ($userIdsStr)";
    if ($conn->query($query)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to archive users']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No user IDs provided']);
}

$conn->close();
