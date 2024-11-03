<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../db.php';
header('Content-Type: application/json');

$response = ['status' => false, 'message' => ''];

try {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        throw new Exception('Invalid input data. Ensure that the request body is correctly formatted.');
    }

    $full_name = $data['full_name'] ?? '';
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $confirm_password = $data['confirm_password'] ?? '';
    $lrn = $data['lrn'] ?? '';
    $user_type = $data['user_type'] ?? 'student';

    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password) || empty($lrn)) {
        throw new Exception('All fields are required!');
    }

    if ($password !== $confirm_password) {
        throw new Exception('Passwords do not match!');
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if the email is already registered
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        throw new Exception('Email already registered!');
    }

    // If registering a parent, ensure that the LRN exists in the students table
    if ($user_type === 'parent') {
        $stmt = $conn->prepare("SELECT id FROM students WHERE lrn = ?");
        $stmt->bind_param('s', $lrn);
        $stmt->execute();
        $student_result = $stmt->get_result();

        if ($student_result->num_rows === 0) {
            throw new Exception('LRN not found. Please enter a valid student LRN.');
        }
        $student = $student_result->fetch_assoc();
        $student_id = $student['id']; // Fetch student ID for linking
    }

    // Determine role
    $role = $user_type === 'parent' ? 'parent' : 'student';

    // Insert into users table
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $full_name, $email, $hashed_password, $role);

    if ($stmt->execute()) {
        $user_id = $conn->insert_id;

        if ($user_type === 'student') {
            $status = 0;
            $stmt_student = $conn->prepare("INSERT INTO students (user_id, full_name, email, lrn, status) VALUES (?, ?, ?, ?, ?)");
            $stmt_student->bind_param('isssi', $user_id, $full_name, $email, $lrn, $status);
            if (!$stmt_student->execute()) {
                throw new Exception("Failed to insert into students table: " . $stmt_student->error);
            }
            $stmt_student->close();
        } else {
            // Register parent and link to student
            $stmt_parent = $conn->prepare("INSERT INTO parents (full_name, email, student_id, password) VALUES (?, ?, ?, ?)");
            if (!$stmt_parent) {
                throw new Exception("Parent insertion prepare failed: " . $conn->error);
            }
            $stmt_parent->bind_param('ssis', $full_name, $email, $student_id, $hashed_password); // Use $student_id for linking
            if (!$stmt_parent->execute()) {
                throw new Exception("Parent insertion failed: " . $stmt_parent->error);
            }
            $stmt_parent->close();
        }

        $response['status'] = true;
        $response['message'] = 'Registration successful! Pending admin approval.';
    } else {
        throw new Exception('Failed to insert into users table.');
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log("Registration Error: " . $e->getMessage());
}

echo json_encode($response);
die();
?>
