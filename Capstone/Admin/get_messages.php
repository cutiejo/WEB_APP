<?php
session_start();
require '../config/firebase.php';

$firebaseService = new FirebaseService();
$database = $firebaseService->getDatabase();

$senderId = $_SESSION['user_id'];
$receiverId = $_GET['receiver_id']; // Teacher ID to view messages with

$messages = $database->getReference("messages/$senderId-$receiverId")->getValue();
echo json_encode($messages);
