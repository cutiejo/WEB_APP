<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../db.php';

// Assuming you have a session variable 'user_id' for the logged-in admin
$user_id = $_SESSION['user_id'] ?? null;

// Check if user_id is set
if ($user_id) {
    // Prepare and execute the query to fetch admin's full name and profile image from the users table
    $query = "SELECT full_name FROM users WHERE id = ? AND role = 'admin'";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $admin_full_name = $row['full_name'];
            
            // Check if profile_image is set; if not, use a default image
            $profile_image = (!empty($row['profile_image'])) ? $row['profile_image'] : '../assets/imgs/default-admin.png';
        } else {
            $admin_full_name = "Admin"; // Default fallback if name not found
            $profile_image = '../assets/imgs/adminppic.png';
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
