<?php
header('Content-Type: application/json');
include '../db.php';

// Check if user_id parameter is provided
if (!isset($_GET['user_id'])) {
    echo json_encode(["error" => "User ID is required"]);
    exit();
}

$user_id = $_GET['user_id'];

try {
    // Prepare the SQL statement
    $stmt = $conn->prepare("SELECT id, message, type, status, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $user_id);

    // Execute the query
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if there are any notifications
    if ($result->num_rows > 0) {
        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
        echo json_encode($notifications);
    } else {
        echo json_encode(["message" => "No notifications found"]);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(["error" => "An error occurred: " . $e->getMessage()]);
}

$conn->close();
?>
