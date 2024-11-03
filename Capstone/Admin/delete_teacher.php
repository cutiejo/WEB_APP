<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ids'])) {
    $ids = $_POST['ids'];

    // Prepare the IDs for the SQL query
    $ids_placeholders = implode(',', array_fill(0, count($ids), '?'));

    $sql = "DELETE FROM teachers WHERE employee_id IN ($ids_placeholders)";
    $stmt = $conn->prepare($sql);

    // Bind the IDs to the placeholders
    $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Teachers deleted successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete teachers.']);
    }
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
?>
