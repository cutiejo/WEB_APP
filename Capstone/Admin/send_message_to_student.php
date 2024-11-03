<?php
session_start();
require '../config/firebase.php';

$firebaseService = new FirebaseService();
$database = $firebaseService->getDatabase();

$senderId = $_SESSION['user_id'];
$receiverId = $_POST['receiver_id']; // Student ID
$messageContent = $_POST['message'];

$messageData = [
    'sender_id' => $senderId,
    'receiver_id' => $receiverId,
    'message_content' => $messageContent,
    'timestamp' => time(),
];

// Push to Firebase Realtime Database
$database->getReference("student_messages/$senderId-$receiverId")->push($messageData);

echo json_encode(["status" => "success", "message" => "Message sent"]);
