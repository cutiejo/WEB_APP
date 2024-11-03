<?php
include '../db.php';

$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ids = $_POST['ids'];
    $ids_placeholder = implode(',', array_fill(0, count($ids), '?'));

    $query = "DELETE FROM students WHERE lrn IN ($ids_placeholder)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param(str_repeat('s', count($ids)), ...$ids);

    if ($stmt->execute()) {
        $response['status'] = 'success';
        $response['message'] = 'Selected students deleted successfully!';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to delete students: ' . $stmt->error;
    }

    $stmt->close();
}

echo json_encode($response);
?>
