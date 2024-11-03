<?php
// Include the database connection and Student model
include '../db.php';
include '../models/Student.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Check if the required fields are set
if (!isset($_POST['user_id']) || empty($_POST['user_id'])) {
    echo json_encode([
        "status" => false,
        "message" => "User ID is required."
    ]);
    exit();
}

// Retrieve and sanitize input values
$user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
$full_name = isset($_POST['full_name']) ? mysqli_real_escape_string($conn, $_POST['full_name']) : '';
$address = isset($_POST['address']) ? mysqli_real_escape_string($conn, $_POST['address']) : '';
$birth_date = isset($_POST['birth_date']) ? mysqli_real_escape_string($conn, $_POST['birth_date']) : '';
$guardian = isset($_POST['guardian']) ? mysqli_real_escape_string($conn, $_POST['guardian']) : '';
$gender = isset($_POST['gender']) ? mysqli_real_escape_string($conn, $_POST['gender']) : '';

// Handle image upload if available
$image_name = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $image = $_FILES['image'];
    $image_name = "profile_{$user_id}_" . time() . ".jpg"; // Unique image name
    $target_path = "../uploads/" . $image_name;

    if (!move_uploaded_file($image['tmp_name'], $target_path)) {
        echo json_encode([
            "status" => false,
            "message" => "Image upload failed."
        ]);
        exit();
    }
}

// Update the student profile in the database
$query = "UPDATE students SET
            full_name = '$full_name',
            address = '$address',
            birth_date = '$birth_date',
            guardian = '$guardian',
            sex = '$gender'";

// If a new image was uploaded, update the image path
if ($image_name) {
    $query .= ", image = '$image_name'";
}

$query .= " WHERE user_id = '$user_id'";

if (mysqli_query($conn, $query)) {
    echo json_encode([
        "status" => true,
        "message" => "Profile updated successfully."
    ]);
} else {
    echo json_encode([
        "status" => false,
        "message" => "Failed to update profile: " . mysqli_error($conn)
    ]);
}

// Close the database connection
mysqli_close($conn);
?>
