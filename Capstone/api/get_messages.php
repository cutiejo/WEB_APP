<?php
include '../db.php';
header('Content-Type: application/json');

$response = ["status" => false, "messages" => []];

try {
    $user_id = $_GET['user_id'];
    $role = $_GET['role'];

    if (empty($user_id) || empty($role)) {
        throw new Exception('User ID and role are required.');
    }

    $stmt = $conn->prepare("SELECT * FROM messages WHERE (receiver_id = ? AND receiver_role = ?) OR (sender_id = ? AND sender_role = ?)");
    $stmt->bind_param("isis", $user_id, $role, $user_id, $role);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response["messages"][] = $row;
        }
        $response["status"] = true;
    } else {
        throw new Exception("Failed to fetch messages.");
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
?>
