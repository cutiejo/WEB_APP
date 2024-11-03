<?php
include '..db/php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $fcm_token = $_POST['fcm_token'];

    $query = "UPDATE users SET fcm_token = ? WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $fcm_token, $user_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "FCM token saved successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to save FCM token"]);
    }
    $stmt->close();
}
$conn->close();
?>