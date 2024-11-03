<?php
include 'db.php'; // Database connection file

header('Content-Type: application/json');

// Get the RFID UID from the query string
$rfid_uid = isset($_GET['rfid_uid']) ? $_GET['rfid_uid'] : '';

if (empty($rfid_uid)) {
    echo json_encode(['status' => 'error', 'message' => 'RFID UID is required']);
    exit;
}

// Query to retrieve student information based on RFID UID
$studentQuery = $conn->prepare("SELECT s.full_name, s.lrn, g.grade_level, sec.section 
                                FROM students s
                                LEFT JOIN grade_levels g ON s.grade_level_id = g.id
                                LEFT JOIN sections sec ON s.section_id = sec.id
                                WHERE s.rfid_uid = ?");
$studentQuery->bind_param("s", $rfid_uid);
$studentQuery->execute();
$studentResult = $studentQuery->get_result();

if ($studentResult->num_rows > 0) {
    $studentData = $studentResult->fetch_assoc();

    // Set a default status indicator (like 'IN' or 'Present')
    $studentData['status_indicator'] = 'IN';

    echo json_encode(['status' => 'success', 'student' => $studentData]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'RFID UID not found']);
}

$studentQuery->close();
$conn->close();
