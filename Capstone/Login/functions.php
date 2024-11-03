<?php
// functions.php

// Function to verify user credentials and role
function login($email, $password, $role) {
    global $conn;

    // Prepare the SQL statement
    $stmt = $conn->prepare("SELECT password FROM users WHERE email = ? AND role = ?");
    $stmt->bind_param("ss", $email, $role);
    
    if ($stmt->execute()) {
        // Get the result
        $result = $stmt->get_result();
        
        // If the user exists
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            // Verify the password
            if (password_verify($password, $row['password'])) {
                return true;
            }
        }
    }

    // Return false if credentials are invalid
    return false;
}
