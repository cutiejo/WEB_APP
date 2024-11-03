<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $teacher_id = $_POST['teacher_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $rfid_uid = $_POST['rfid_uid'];

    // Check for required fields
    if (empty($teacher_id) || empty($name) || empty($email) || empty($phone) || empty($rfid_uid)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid data provided.']);
        exit();
    }

    // Handle image upload if provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_tmp = $_FILES['image']['tmp_name'];
        $image_name = basename($_FILES['image']['name']);
        $upload_dir = '../uploads/';
        $image_path = $upload_dir . $image_name;

        // Move the uploaded file to the uploads directory
        if (move_uploaded_file($image_tmp, $image_path)) {
            // Update teacher data with the new image
            $stmt = $conn->prepare("UPDATE teachers SET name = ?, email = ?, phone = ?, rfid_uid = ?, image = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $name, $email, $phone, $rfid_uid, $image_path, $teacher_id);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to upload image.']);
            exit();
        }
    } else {
        // Update teacher data without changing the image
        $stmt = $conn->prepare("UPDATE teachers SET name = ?, email = ?, phone = ?, rfid_uid = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $name, $email, $phone, $rfid_uid, $teacher_id);
    }

    // Execute the update and check for success
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'teacher_id' => $teacher_id]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error updating teacher data: ' . $conn->error]);
    }

    $stmt->close();
    $conn->close();
}
?>
