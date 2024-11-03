<?php
// Include Composer's autoload file
require_once '../vendor/autoload.php'; 

// Include your database connection or any necessary configuration
include '../db.php'; // Adjust the path as necessary
include 'functions.php'; // If you have additional functions, include them

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['idtoken'])) {
        $id_token = $_POST['idtoken'];

        // Initialize the Google Client with your client ID
        $client = new Google_Client(['client_id' => '
479928332833-j3qqdcpdo8h3v4p88ah4pdd3kvctc5ra.apps.googleusercontent.com']); // Replace with your actual Client ID
        $payload = $client->verifyIdToken($id_token);

        if ($payload) {
            $userid = $payload['sub']; // User's Google ID
            $email = $payload['email'];
            $name = $payload['name'];

            // Check if the user already exists in your database
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // User exists, log them in
                // Fetch the role of the user
                $user = $result->fetch_assoc();
                $role = $user['role'];

                // Redirect based on role
                if ($role == 'teacher') {
                    header("Location: ../Teacher/dashboard.php");
                } elseif ($role == 'admin') {
                    header("Location: ../Admin/dashboard.php");
                } elseif ($role == 'student') {
                    header("Location: ../Student/dashboard.php");
                } elseif ($role == 'parent') {
                    header("Location: ../Parent/dashboard.php");
                }
            } else {
                // User does not exist, register them
                // You might want to assign a default role here, e.g., 'student' or 'parent'
                $default_role = 'student'; // Change this to your default role

                // Insert the new user into the database
                $stmt = $conn->prepare("INSERT INTO users (full_name, email, google_id, role) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $name, $email, $userid, $default_role);
                
                if ($stmt->execute()) {
                    // Redirect to dashboard after successful registration
                    header("Location: ../Student/dashboard.php"); // Adjust this as necessary
                } else {
                    echo "Error: " . $stmt->error;
                }
            }
        } else {
            // Invalid ID token
            echo "Invalid ID token";
        }
    } else {
        echo "ID token not provided.";
    }
} else {
    echo "Invalid request method.";
}
?>
