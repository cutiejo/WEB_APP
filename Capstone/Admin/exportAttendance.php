<?php
include '../db.php';

// Get filter values from query string
$role = $_GET['role'];
$grade_level = $_GET['grade_level'];
$section = $_GET['section'];
$from = $_GET['from'];
$to = $_GET['to'];

// Base query
$query = "SELECT * FROM attendance WHERE 1=1";

// Apply filters if values are provided
if ($role != 'All') {
    $query .= " AND role = '$role'";
}
if (!empty($grade_level)) {
    $query .= " AND grade_level = '$grade_level'";
}
if (!empty($section)) {
    $query .= " AND section = '$section'";
}
if (!empty($from)) {
    $query .= " AND scan_time >= '$from'";
}
if (!empty($to)) {
    $query .= " AND scan_time <= '$to'";
}

$result = $conn->query($query);

// Set headers for CSV file download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=attendance_records.csv');

// Open output stream
$output = fopen('php://output', 'w');

// Output column headings
fputcsv($output, array('Attendance ID', 'User ID', 'RFID Tag', 'Full Name', 'Role', 'Grade Level', 'Section', 'Scan Time', 'Event Type', 'Status'));

// Output rows
while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}

// Close output stream
fclose($output);
?>


