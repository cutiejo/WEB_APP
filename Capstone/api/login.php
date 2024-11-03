<?php
include '../db.php';  // Ensure the database connection
header('Content-Type: application/json'); // Set header for JSON response

$response = ['status' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true); // Decoding the JSON payload
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $role = $data['role'] ?? '';

    // Check if all fields are provided
    if (empty($email) || empty($password) || empty($role)) {
        $response['message'] = 'All fields are required.';
    } else {
        // Query to check if the user exists and is not archived
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = ? AND archived = 0");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("ss", $email, $role);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify the password
            if (password_verify($password, $user['password'])) {
                $response['status'] = true;
                $response['message'] = 'Login successful';
                $response['user'] = [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ];
            } else {
                $response['message'] = 'Invalid email or password.';
            }
        } else {
            $response['message'] = 'User not found or archived.';
        }

        $stmt->close();
    }
    $conn->close();
}

// Send JSON response
echo json_encode($response);
?>
