<?php
include '../db.php';
header('Content-Type: application/json');

$response = ['status' => false, 'student' => []];

try {
    $user_id = $_GET['user_id'] ?? '';

    if (empty($user_id)) {
        throw new Exception('User ID is required.');
    }

    // SQL Query to fetch student and linked parent data
    $query = "SELECT 
                students.*, 
                parents.full_name AS parent_name, 
                parents.email AS parent_email, 
                parents.phone AS parent_contact, 
                parents.address AS parent_address 
              FROM students
              LEFT JOIN parents ON students.id = parents.student_id
              WHERE students.user_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Student not found.');
    }

    $response['student'] = $result->fetch_assoc();
    $response['status'] = true;

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
