<?php
// Include the database connection
include '../db.php';
include '../models/Student.php';

// Set content type to JSON for API response
header('Content-Type: application/json');

// Check if 'user_id' is provided in the request
if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
    echo json_encode([
        "status" => false,
        "message" => "User ID not provided or is empty."
    ]);
    exit();
}

// Sanitize the user_id to prevent SQL injection
$user_id = mysqli_real_escape_string($conn, $_GET['user_id']);

// Prepare and execute the SQL query to fetch student and parent information based on user_id
$query = "SELECT students.id, students.user_id, students.lrn, students.full_name AS student_full_name, 
                 students.email, students.birth_date, students.rfid_uid, students.address, students.sex, 
                 students.guardian, students.contact, students.image, grade_levels.grade_level, 
                 sections.section, parents.full_name AS parent_full_name
          FROM students
          LEFT JOIN grade_levels ON students.grade_level_id = grade_levels.id
          LEFT JOIN sections ON students.section_id = sections.id
          LEFT JOIN parents ON students.id = parents.student_id
          WHERE students.user_id = '$user_id'";

$result = mysqli_query($conn, $query);

// Check if any student is found
if ($result && mysqli_num_rows($result) > 0) {
    $student = mysqli_fetch_assoc($result);

    echo json_encode([
        "status" => true,
        "student" => [
            "id" => $student['id'],
            "user_id" => $student['user_id'],
            "lrn" => $student['lrn'],
            "full_name" => $student['student_full_name'],
            "email" => $student['email'],
            "birth_date" => $student['birth_date'],
            "rfid_uid" => $student['rfid_uid'],
            "address" => $student['address'],
            "sex" => $student['sex'],
            "contact" => $student['contact'],
            "image" => $student['image'],
            "grade_level" => $student['grade_level'] ?? "No Grade Assigned",
            "section" => $student['section'] ?? "No Section Assigned",
            "parent_full_name" => $student['parent_full_name'] ?? "No Parent Linked"
        ]
    ]);
} else {
    // If no student is found, send an appropriate response
    echo json_encode([
        "status" => false,
        "message" => "Student not found in the database."
    ]);
}

// Close the database connection
mysqli_close($conn);
