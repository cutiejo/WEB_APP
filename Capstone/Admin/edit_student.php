<?php
include '../db.php';

if (isset($_POST['lrn'])) {
    $lrn = $_POST['lrn'];
    $name = $_POST['name'];
    $birth_date = $_POST['birth_date'];
    $rfid_uid = $_POST['rfid_uid'];
    $age = $_POST['age'];
    $address = $_POST['address'];
    $sex = $_POST['sex'];
    $grade_level_id = $_POST['grade_level_id'];
    $section_id = $_POST['section_id'];
    $guardian = $_POST['guardian'];
    $contact = $_POST['contact'];
    $status = $_POST['status'];
    $image_path = null;

    // Check if a new image is uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "../uploads/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = 'uploads/' . basename($_FILES["image"]["name"]);
        }
    }

    // Update student information
    $update_query = "UPDATE students SET full_name = '$name', birth_date = '$birth_date', rfid_uid = '$rfid_uid', age = '$age', 
                     address = '$address', sex = '$sex', grade_level_id = '$grade_level_id', section_id = '$section_id', 
                     guardian = '$guardian', contact = '$contact', status = '$status'";

    // Include image in update if uploaded
    if ($image_path) {
        $update_query .= ", image = '$image_path'";
    }

    $update_query .= " WHERE lrn = '$lrn'";

    if ($conn->query($update_query)) {
        // Return success response
        echo json_encode(['status' => 'success', 'message' => 'Student updated successfully']);
    } else {
        // Return error response
        echo json_encode(['status' => 'error', 'message' => 'Failed to update student']);
    }
} else {
    // Return error if required data is missing
    echo json_encode(['status' => 'error', 'message' => 'Required data missing']);
}
?>
