<?php
include '../db.php';
header('Content-Type: application/json');

$response = ["status" => false];

try {
    $message_id = $_POST['message_id'];

    if (empty($message_id)) {
        throw new Exception('Message ID is required.');
    }

    $stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE id = ?");
    $stmt->bind_param("i", $message_id);

    if ($stmt->execute()) {
        $response["status"] = true;
        $response["message"] = "Message marked as read.";
    } else {
        throw new Exception("Failed to mark message as read.");
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
?>