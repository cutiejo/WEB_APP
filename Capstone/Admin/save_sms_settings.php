<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../Login/login.php");
    exit();
}

include '../db.php'; // Include database connection file

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve POST data
    $sender_name = mysqli_real_escape_string($conn, $_POST['sender_name']);
    $notification_status = mysqli_real_escape_string($conn, $_POST['notification_status']);
    $notification_event = mysqli_real_escape_string($conn, $_POST['notification_event']);
    $parent_notification = mysqli_real_escape_string($conn, $_POST['parent_notification']);
    $sms_template = mysqli_real_escape_string($conn, $_POST['sms_template']);

    // Update or insert the settings into the `sms_settings` table
    $sql = "UPDATE sms_settings SET 
                sender_name = '$sender_name', 
                notification_status = '$notification_status', 
                notification_event = '$notification_event', 
                parent_notification = '$parent_notification', 
                sms_template = '$sms_template'
            WHERE id = 1"; // Assuming the row ID is 1 for this example

    if (mysqli_query($conn, $sql)) {
        // Redirect back to settings page with a success message
        header("Location: settings.php?success=sms_settings_saved&active_panel=smsNotification");
    } else {
        // Redirect back with a failure message
        header("Location: settings.php?success=sms_failed&active_panel=smsNotification");
    }
    exit();
} else {
    // Redirect back if accessed directly without POST data
    header("Location: settings.php?active_panel=smsNotification");
    exit();
}
