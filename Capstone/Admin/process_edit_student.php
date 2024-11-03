<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit();
}

$lrn = mysqli_real_escape_string($conn, $_POST['lrn']);
$full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
$birth_date = mysqli_real_escape_string($conn, $_POST['birth_date']);
$rfid_uid = mysqli_real_escape_string($conn, $_POST['rfid_uid']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$contact = mysqli_real_escape_string($conn, $_POST['contact']);
$guardian = mysqli_real_escape_string($conn, $_POST['guardian']);
$address = mysqli_real_escape_string($conn, $_POST['address']);
$sex = mysqli_real_escape_string($conn, $_POST['sex']);
$status = mysqli_real_escape_string($conn, $_POST['status']);
$grade_level_id = mysqli_real_escape_string($conn, $_POST['grade_level_id']);
$section_id = mysqli_real_escape_string($conn, $_POST['section_id']);

$original_query = "SELECT * FROM students WHERE lrn = '$lrn'";
$original_result = $conn->query($original_query);
$original = $original_result->fetch_assoc();

$imagePath = '';
if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $targetDir = "../uploads/";
    $fileName = "profile_".$lrn."_".time()."." . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $targetFilePath = $targetDir . $fileName;
    $allowedTypes = ['jpg', 'png', 'jpeg', 'gif'];
    
    if (in_array(strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION)), $allowedTypes)) {
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
            $imagePath = "uploads/" . $fileName;
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to upload image."]);
            exit();
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid file format."]);
        exit();
    }
}

$updateQuery = "UPDATE students SET 
    full_name = '$full_name', 
    birth_date = '$birth_date', 
    rfid_uid = '$rfid_uid', 
    email = '$email', 
    contact = '$contact', 
    guardian = '$guardian', 
    address = '$address', 
    sex = '$sex', 
    status = '$status', 
    grade_level_id = '$grade_level_id', 
    section_id = '$section_id'";

// Append image path if a new image was uploaded
if ($imagePath) {
    $updateQuery .= ", image = '$imagePath'";
}

$updateQuery .= " WHERE lrn = '$lrn'";

if ($conn->query($updateQuery) === TRUE) {
    // Check for changes and prepare an email if needed
    $changes = [];
    if ($original['grade_level_id'] != $grade_level_id) {
        $changes[] = "Grade Level changed to {$grade_level_id}.";
    }
    if ($original['section_id'] != $section_id) {
        $changes[] = "Section changed to {$section_id}.";
    }
    if ($original['status'] != $status) {
        $statusText = $status == 1 ? 'Approved' : ($status == 2 ? 'Rejected' : 'Pending');
        $changes[] = "Status changed to {$statusText}.";
    }

    if (!empty($changes)) {
        $to = $email;
        $subject = "Profile Update Notification";
        $message = "Dear $full_name,\n\nYour profile has been updated with the following changes:\n";
        $message .= implode("\n", $changes) . "\n\n";
        $message .= "Please log in to your account to review these changes.\n\nBest regards,\nSchool Administration";
        
        $headers = "From: admin@school.com";

        if (!mail($to, $subject, $message, $headers)) {
            error_log("Failed to send email to $email.");
        }
    }

    // Insert in-app notification for the student
    $user_id = $original['user_id'];
    $notificationMessage = "Your profile was updated by the admin. Please review the changes.";
    $insertNotification = "INSERT INTO notifications (user_id, message) VALUES ('$user_id', '$notificationMessage')";
    $conn->query($insertNotification);

    echo json_encode(["status" => "success", "message" => "Student updated successfully."]);
} else {
    echo json_encode(["status" => "error", "message" => "Error updating student: " . $conn->error]);
}

$conn->close();
?>
