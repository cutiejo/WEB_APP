<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the teacher ID to update
    $teacher_id = $_POST['teacher_id'];

    // Fetch user_id from the teachers table
    $result = $conn->query("SELECT user_id, image FROM teachers WHERE id = $teacher_id");
    $teacher = $result->fetch_assoc();
    $user_id = $teacher['user_id'];
    $existing_image = $teacher['image']; // Existing image path

    // Get POST data from the form
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $rfid_uid = $_POST['rfid_uid'];
    $grade_level_id = $_POST['grade_level_id'];
    $section_id = $_POST['section_id'];

    // Handle image upload if a new image is provided
    $image_path = $existing_image; // Default to existing image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_name = $_FILES['image']['name'];
        $target_dir = "../uploads/";
        $target_file = $target_dir . basename($image_name);

        // Move the uploaded file to the target directory
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = 'uploads/' . basename($image_name); // New image path
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error uploading image']);
            exit();
        }
    }

    // Update users table (you may want to include password update if applicable)
    $update_users = "UPDATE users SET full_name = '$full_name', email = '$email' WHERE id = '$user_id'";
    if ($conn->query($update_users)) {
        // Update teachers table with the new data
        $update_teachers = "
            UPDATE teachers 
            SET full_name = '$full_name', email = '$email', phone = '$phone', rfid_uid = '$rfid_uid', 
                grade_level_id = '$grade_level_id', section_id = '$section_id', image = '$image_path'
            WHERE id = '$teacher_id'";
        
        if ($conn->query($update_teachers)) {
            echo json_encode(['status' => 'success', 'message' => 'Teacher updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error updating teacher details']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error updating user details']);
    }
}
?>
