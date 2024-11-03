<?php
include '../db.php';

ini_set('display_errors', 0); // Prevent error output from corrupting JSON
header('Content-Type: application/json'); // Set the header to JSON

// Retrieve user inputs
$fullName = trim($_POST['full_name']);
$email = trim($_POST['email']);
$password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT); // Hash the password
$role = $_POST['role'];

// Check if email or full name already exists
$query = "SELECT email, full_name FROM users WHERE email = ? OR full_name = ?";
$stmt = $conn->prepare($query);

if ($stmt) {
    $stmt->bind_param('ss', $email, $fullName);
    $stmt->execute();
    $result = $stmt->get_result();

    // Log the number of rows returned
    error_log("Number of rows found: " . $result->num_rows); // This will log to the server's error log

    // Check if any records were found
    if ($result->num_rows > 0) {
        // Fetch the record to see what caused the duplicate
        $row = $result->fetch_assoc();
        if ($row['email'] == $email) {
            echo json_encode(['success' => false, 'duplicate' => 'email']);
        } elseif ($row['full_name'] == $fullName) {
            echo json_encode(['success' => false, 'duplicate' => 'fullname']);
        }
    } else {
        // No duplicates found, proceed to insert new user
        $insertQuery = "INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertQuery);

        if ($insertStmt) {
            $insertStmt->bind_param('ssss', $fullName, $email, $password, $role);

            if ($insertStmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Database insertion failed']);
            }

            $insertStmt->close();
        }
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Query preparation failed: ' . $conn->error]);
}

$conn->close();
?>
