<?php
// api/update_password.php

include '../db.php';
$data = json_decode(file_get_contents("php://input"), true);

// Check for required fields
if (!isset($data['user_id']) || !isset($data['old_password']) || !isset($data['new_password'])) {
    echo json_encode([
        "status" => false,
        "message" => "Required fields are missing."
    ]);
    exit();
}

$user_id = mysqli_real_escape_string($conn, $data['user_id']);
$old_password = mysqli_real_escape_string($conn, $data['old_password']);
$new_password = mysqli_real_escape_string($conn, $data['new_password']);

// Verify the old password
$query = "SELECT password FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);
if (mysqli_num_rows($result) === 0) {
    echo json_encode([
        "status" => false,
        "message" => "User not found."
    ]);
    exit();
}

$row = mysqli_fetch_assoc($result);
if (!password_verify($old_password, $row['password'])) {
    echo json_encode([
        "status" => false,
        "message" => "Old password is incorrect."
    ]);
    exit();
}

// Update to the new password
$new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
$update_query = "UPDATE users SET password = '$new_password_hashed' WHERE id = '$user_id'";
if (mysqli_query($conn, $update_query)) {
    echo json_encode([
        "status" => true,
        "message" => "Password updated successfully."
    ]);
} else {
    echo json_encode([
        "status" => false,
        "message" => "Failed to update password."
    ]);
}

mysqli_close($conn);
?>