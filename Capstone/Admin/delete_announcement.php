<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../db.php';

$response = ["status" => "error", "message" => "Something went wrong."];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $response = ["status" => "success", "message" => "Announcement deleted successfully!"];
        } else {
            $response = ["status" => "error", "message" => "Failed to delete announcement."];
        }
        $stmt->close();
    } else {
        $response = ["status" => "error", "message" => "Invalid announcement ID."];
    }
}

echo json_encode($response);
?>
