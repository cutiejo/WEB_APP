<?php
include '../db.php';

header("Content-Type: application/json");


  
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Decode JSON input
        $input = json_decode(file_get_contents("php://input"), true);
        $user_id = $input['user_id'] ?? null;
    
        // Check if user_id is provided
        if ($user_id) {
            // Update all unread notifications for the user to 'read' status
            $query = "UPDATE notifications SET status = 'read' WHERE user_id = ? AND status = 'unread'";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $user_id);
    
            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "Notifications marked as read"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to mark notifications as read"]);
            }
            $stmt->close();
        } else {
            echo json_encode(["status" => "error", "message" => "user_id parameter is missing"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    }
    
    $conn->close();
    ?>
    