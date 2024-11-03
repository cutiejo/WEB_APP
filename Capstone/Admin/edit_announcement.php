<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure the response is JSON
header('Content-Type: application/json');

include '../db.php';

$response = ["status" => "error", "message" => "Something went wrong."];

// Check for POST request and required 'id' field
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $postingDate = $_POST['postingDate'];
    $status = isset($_POST['status']) ? 1 : 0;

    // Default image handling
    $image = $_POST['currentImage'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../uploads/";
        $image = basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image;
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $response = ["status" => "error", "message" => "Error uploading the file."];
            echo json_encode($response);
            exit();
        }
    }

    // Prepare and execute update statement
    $stmt = $conn->prepare("UPDATE announcements SET title = ?, content = ?, posting_date = ?, status = ?, image = ? WHERE id = ?");
    $stmt->bind_param("sssisi", $title, $content, $postingDate, $status, $image, $id);

    if ($stmt->execute()) {
        $response = ["status" => "success", "message" => "Announcement updated successfully!"];
    } else {
        $response = ["status" => "error", "message" => "Failed to update the announcement."];
    }

    $stmt->close();
} else {
    $response = ["status" => "error", "message" => "Invalid request."];
}

// Output the response as JSON
echo json_encode($response);
$conn->close();
?>
