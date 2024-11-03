<?php

header('Content-Type: application/json');
include_once '../db.php'; // Include your database configuration file here

// Check if user_id is provided in the GET request
if (isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']); // Ensure the user_id is an integer

  
    // Check for connection errors
    if ($conn->connect_error) {
        echo json_encode([
            "status" => false,
            "message" => "Database connection failed: " . $conn->connect_error
        ]);
        exit();
    }

    // Prepare the SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM parents WHERE parent_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the parent record was found
    if ($result->num_rows > 0) {
        $parent = $result->fetch_assoc();
        echo json_encode([
            "status" => true,
            "parent" => $parent
        ]);
    } else {
        echo json_encode([
            "status" => false,
            "message" => "Parent profile not found."
        ]);
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
} else {
    echo json_encode([
        "status" => false,
        "message" => "No user_id provided."
    ]);
}
