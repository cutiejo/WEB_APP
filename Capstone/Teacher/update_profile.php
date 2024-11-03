<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
    $rfid_tag = isset($_POST['rfid_tag']) ? $_POST['rfid_tag'] : '';
    $grade_level_id = isset($_POST['grade_level_id']) ? $_POST['grade_level_id'] : '';
    $section_id = isset($_POST['section_id']) ? $_POST['section_id'] : '';

    // Handle image upload
    $image_name = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
        $image_dir = __DIR__ . '/uploads/'; // Use absolute path
        $image_name = basename($_FILES['profile_image']['name']);
        $image_path = $image_dir . $image_name;

        // Check if the directory exists, if not, create it
        if (!is_dir($image_dir)) {
            mkdir($image_dir, 0755, true); // Create the directory with proper permissions
        }

        // Move uploaded file to the target directory
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $image_path)) {
            // Image uploaded successfully
        } else {
            echo "Failed to upload image.";
            exit;
        }
    } else {
        // Handle any file upload errors, or if no file was uploaded
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            echo "Upload failed with error code: " . $_FILES['profile_image']['error'];
            exit;
        }
        // If no file uploaded, $image_name will remain null, meaning no image update
    }

    // Assuming you have a session variable that holds the teacher's ID
    $teacher_id = 1; // Replace this with actual logic to get the teacher's ID

    // Update query
    $sql = "UPDATE teachers SET name = ?, email = ?, phone = ?, rfid_tag = ?, grade_level_id = ?, section_id = ?";

    // If the image was uploaded, include it in the query
    if ($image_name !== null) {
        $sql .= ", image = ?";
    }

    $sql .= " WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        // If the image was uploaded, bind the image parameter
        if ($image_name !== null) {
            $stmt->bind_param("ssssissi", $name, $email, $phone, $rfid_tag, $grade_level_id, $section_id, $image_name, $teacher_id);
        } else {
            $stmt->bind_param("sssssii", $name, $email, $phone, $rfid_tag, $grade_level_id, $section_id, $teacher_id);
        }

        // Execute the statement
        if ($stmt->execute()) {
            echo "Profile updated successfully.";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }

    // Close the database connection
    $conn->close();
}
?>
