<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get POST data from form
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Secure the password
    $phone = $_POST['phone'];
    $rfid_uid = $_POST['rfid_uid'];
    $grade_level_id = $_POST['grade_level_id'];
    $section_id = $_POST['section_id'];

    // Handle image upload if exists
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_name = $_FILES['image']['name'];
        $target_dir = "../uploads/";
        $target_file = $target_dir . basename($image_name);

        // Move the uploaded file to the target directory
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = 'uploads/' . basename($image_name); // Store relative path
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error uploading image']);
            exit();
        }
    }

    // INSERT INTO users table
    $query_users = "INSERT INTO users (full_name, email, password, role) 
                    VALUES ('$full_name', '$email', '$password', 'teacher')";
    if ($conn->query($query_users)) {
        // Get last inserted ID from users table
        $user_id = $conn->insert_id;

        // INSERT INTO teachers table with the image path if uploaded
        $query_teachers = "INSERT INTO teachers (user_id, full_name, email, phone, rfid_uid, grade_level_id, section_id, image) 
                           VALUES ('$user_id', '$full_name', '$email', '$phone', '$rfid_uid', '$grade_level_id', '$section_id', '$image_path')";
        if ($conn->query($query_teachers)) {
            echo json_encode(['status' => 'success', 'message' => 'Teacher added successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error adding teacher in teachers table']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error adding teacher in users table']);
    }
}
?>
