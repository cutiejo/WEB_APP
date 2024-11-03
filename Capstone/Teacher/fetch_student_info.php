<?php
include '../db.php';

// Get the student name from the POST request
$name = isset($_POST['name']) ? $_POST['name'] : '';

// Initialize response array
$response = [
    'grade_level_id' => '',
    'section_id' => ''
];

if (!empty($name)) {
    // Prepare the query to fetch the student's grade level and section
    $stmt = $conn->prepare("SELECT grade_level_id, section_id FROM students WHERE name = ?");
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch student information if found
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $response['grade_level_id'] = $row['grade_level_id'];
        $response['section_id'] = $row['section_id'];
    }

    $stmt->close();
}

$conn->close();

// Return the response as JSON
echo json_encode($response);
?>
