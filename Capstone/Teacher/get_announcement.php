<?php
include '../db.php'; // include database connection

// Check if the ID is passed in the request
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Fetch the specific announcement details from the database
    $query = "SELECT * FROM announcements WHERE id = $id";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $announcement = $result->fetch_assoc();
        echo json_encode($announcement);
    } else {
        echo json_encode(['error' => 'Announcement not found']);
    }
} else {
    echo json_encode(['error' => 'ID not provided']);
}
?>