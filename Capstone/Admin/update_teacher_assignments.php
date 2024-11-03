<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get POST data from the form
    $teacher_id = $_POST['teacher_id'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $rfid_uid = $_POST['rfid_uid'];
    $grade_level_id = $_POST['grade_level_id'];
    $section_id = $_POST['section_id'];

    // Check if an image was uploaded
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

    // Update the teachers table
    $query = "UPDATE teachers SET 
                full_name = '$full_name', 
                email = '$email', 
                phone = '$phone', 
                rfid_uid = '$rfid_uid', 
                grade_level_id = '$grade_level_id', 
                section_id = '$section_id'";

    // Add the image path to the query if a new image was uploaded
    if ($image_path !== null) {
        $query .= ", image = '$image_path'";
    }

    $query .= " WHERE id = '$teacher_id'";

    if ($conn->query($query)) {
        echo json_encode(['status' => 'success', 'message' => 'Teacher updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error updating teacher']);
    }
}
?>
