<?php
// Include the Student model and database connection
require_once '../models/Student.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if 'user_id' parameter is provided
if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
    echo json_encode([
        "status" => false,
        "message" => "User ID not provided or is empty."
    ]);
    exit();
}

// Retrieve the user_id from the request
$user_id = $_GET['user_id'];

try {
    // Instantiate the Student model and fetch student data
    $studentModel = new Student();
    $studentData = $studentModel->getStudentByUserId($user_id);

    if ($studentData) {
        echo json_encode([
            "status" => true,
            "student" => $studentData
        ]);
    } else {
        echo json_encode([
            "status" => false,
            "message" => "Student not found."
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        "status" => false,
        "message" => "An error occurred: " . $e->getMessage()
    ]);
}
