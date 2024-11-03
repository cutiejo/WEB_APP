<?php
// Include database connection
include '../db.php';

// Check if the form fields are set and not empty
if (isset($_POST['full_name'], $_POST['email'], $_POST['password'], $_POST['lrn'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Encrypt password
    $lrn = mysqli_real_escape_string($conn, $_POST['lrn']);

    // Check if the student exists with the given LRN
    $studentQuery = "SELECT id FROM students WHERE lrn = '$lrn'";
    $studentResult = mysqli_query($conn, $studentQuery);

    if (mysqli_num_rows($studentResult) > 0) {
        $studentRow = mysqli_fetch_assoc($studentResult);
        $student_id = $studentRow['id'];

        // Insert the parent record with the linked student_id
        $parentQuery = "INSERT INTO parents (full_name, email, password, student_id) VALUES ('$full_name', '$email', '$password', '$student_id')";
        if (mysqli_query($conn, $parentQuery)) {
            echo json_encode(["status" => true, "message" => "Parent registered successfully and linked to student."]);
        } else {
            echo json_encode(["status" => false, "message" => "Failed to register parent: " . mysqli_error($conn)]);
        }
    } else {
        echo json_encode(["status" => false, "message" => "No student found with the provided LRN."]);
    }
} else {
    echo json_encode(["status" => false, "message" => "All fields are required."]);
}

// Close database connection
mysqli_close($conn);
?>