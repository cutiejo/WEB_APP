<?php
header('Content-Type: application/json');

require '../db.php';

$action = $_GET['action'];

if ($action == 'send') {
    $senderId = $_POST['sender_id'];
    $receiverId = $_POST['receiver_id'];
    $message = $_POST['message'];

    $query = "INSERT INTO chat (sender_id, receiver_id, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iis", $senderId, $receiverId, $message);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send message']);
    }

    $stmt->close();
} elseif ($action == 'retrieve') {
    $userId = $_GET['user_id'];
    $query = "SELECT * FROM chat WHERE receiver_id = ? OR sender_id = ? ORDER BY timestamp ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $userId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];

    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }

    echo json_encode(['success' => true, 'messages' => $messages]);

    $stmt->close();
}

$conn->close();
?>
