<?php
include '../db.php';
header('Content-Type: application/json');

$response = ['status' => false, 'parents' => []];

try {
    $user_id = $_GET['user_id'] ?? '';

    if (empty($user_id)) {
        throw new Exception('User ID is required.');
    }

    // Fetch student based on user_id
    $studentQuery = "SELECT id FROM students WHERE user_id = ?";
    $stmt = $conn->prepare($studentQuery);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $studentResult = $stmt->get_result();
    $student = $studentResult->fetch_assoc();

    if (!$student) {
        throw new Exception('Student not found.');
    }

    $student_id = $student['id'];

    // Fetch parents/guardians linked to this student
    $parentQuery = "SELECT full_name, email, phone, address FROM parents WHERE student_id = ?";
    $stmt = $conn->prepare($parentQuery);
    $stmt->bind_param('i', $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $parents = [];
    $index = 1;
    while ($row = $result->fetch_assoc()) {
        $parents["guardian$index"] = [
            'name' => $row['full_name'],
            'email' => $row['email'],
            'phone' => $row['phone'],
            'address' => $row['address']
        ];
        $index++;
    }

    if (empty($parents)) {
        throw new Exception('No parent information found for this student.');
    }

    $response['status'] = true;
    $response['parents'] = $parents;
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
