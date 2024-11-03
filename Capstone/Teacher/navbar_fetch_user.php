<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../db.php';

// Assuming you have a session variable 'user_id' for the logged-in teacher
$user_id = $_SESSION['user_id'] ?? null;

// Check if user_id is set
if ($user_id) {
    // Prepare and execute the query to fetch teacher's full name and profile image from the teachers table
    $query = "SELECT full_name, image FROM teachers WHERE user_id = ?";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $teacher_full_name = $row['full_name'];
            $profile_image = $row['image'] ? $row['image'] : '../assets/imgs/default-user.png'; // Default image if none is uploaded
        } else {
            $teacher_full_name = "Teacher"; // Default fallback if name not found
            $profile_image = '../assets/imgs/default-user.png';
        }
    } else {
        // Display the error if query preparation fails
        echo "Database query error: " . $conn->error;
        exit;
    }
} else {
    echo "User ID not set in session.";
    exit;
}
?>
