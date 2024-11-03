<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Encrypt the password
    $rfid_uid = $_POST['rfid_uid'];
    $grade_level_id = $_POST['grade_level_id'];
    $section_id = $_POST['section_id'];
    
    // Optional fields for image upload
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $imagePath = '../uploads/' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
        $image = $imagePath;
    }
    
    $sql = "INSERT INTO students (full_name, email, password, rfid_uid, grade_level_id, section_id, image) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssiss", $full_name, $email, $password, $rfid_uid, $grade_level_id, $section_id, $image);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Student added successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error adding student.']);
    }
    $stmt->close();
}
?>
