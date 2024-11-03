<?php
include '../db.php';

if (isset($_POST['id'])) {
    $message_id = intval($_POST['id']);
    $message = $conn->query("SELECT * FROM messages WHERE id = $message_id")->fetch_assoc();

    if ($message) {
        echo "<h5>From: " . htmlspecialchars($message['sender_name']) . "</h5>";
        echo "<p><strong>Subject:</strong> " . htmlspecialchars($message['subject']) . "</p>";
        echo "<p><strong>Sent:</strong> " . date('d M Y, h:i A', strtotime($message['date_sent'])) . "</p>";
        echo "<hr>";
        echo "<p>" . nl2br(htmlspecialchars($message['content'])) . "</p>";
    } else {
        echo "Message not found.";
    }
}
?>
