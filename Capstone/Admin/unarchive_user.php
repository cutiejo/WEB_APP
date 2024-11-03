<?php
include '../db.php';

ini_set('display_errors', 0); // Prevent error output from corrupting JSON
header('Content-Type: application/json');

// Start output buffering to prevent any unexpected output
ob_clean(); 

$response = [];

if (isset($_POST['id'])) {
    $userId = $_POST['id'];

    // Prepare the query to unarchive the user
    $query = "UPDATE users SET archived = 0 WHERE id = ?";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param('i', $userId);

        if ($stmt->execute()) {
            $response = ['success' => true]; // Send success response as JSON
        } else {
            $response = ['success' => false, 'error' => 'Failed to execute query: ' . $stmt->error];
        }

        $stmt->close();
    } else {
        $response = ['success' => false, 'error' => 'Failed to prepare query: ' . $conn->error];
    }
} else {
    $response = ['success' => false, 'error' => 'No user ID provided'];
}

$conn->close();  // Close the database connection

// Output the JSON response
echo json_encode($response);
exit();  // Ensure the script terminates here without extra output
?>
