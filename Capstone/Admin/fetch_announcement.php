<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json'); // Ensure JSON response

include '../db.php';

$response = ["status" => "error", "message" => "Something went wrong."];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];

    $stmt = $conn->prepare("SELECT * FROM announcements WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $announcement = $result->fetch_assoc();
        echo json_encode($announcement);
    } else {
        $response = ["status" => "error", "message" => "Announcement not found."];
        echo json_encode($response);
    }

    $stmt->close();
} else {
    $response = ["status" => "error", "message" => "Invalid request."];
    echo json_encode($response);
}

$conn->close();
?>
