<?php
include '../db.php';
session_start();

// Ensure the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit();
}

// Check if the request is a POST request with the required data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['lrn_list'])) {
    $action = $_POST['action'];
    $lrn_list = $_POST['lrn_list'];
    $status = ($action === 'approve') ? 1 : 2;

    // Check if LRN list is an array and not empty
    if (!is_array($lrn_list) || empty($lrn_list)) {
        echo json_encode(['status' => 'error', 'message' => 'No students selected.']);
        exit();
    }

    // Prepare placeholders and query for bulk update
    $placeholders = implode(',', array_fill(0, count($lrn_list), '?'));
    $sql = "UPDATE students SET status = ? WHERE lrn IN ($placeholders)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'SQL preparation error: ' . $conn->error]);
        exit();
    }

    // Bind parameters (status + all LRNs)
    $types = str_repeat('i', count($lrn_list) + 1); // One "i" for status, others for LRNs
    $stmt->bind_param($types, $status, ...$lrn_list);

    // Execute and respond with success or error message
    if ($stmt->execute()) {
        $message = ($action === 'approve') ? "Students approved successfully." : "Students rejected successfully.";
        echo json_encode(['status' => 'success', 'message' => $message]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update students: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
