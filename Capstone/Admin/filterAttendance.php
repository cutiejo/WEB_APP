<?php
include '../db.php';

$role = $_POST['role'];
$grade_level = $_POST['grade_level'];
$section = $_POST['section'];
$from = $_POST['from'];
$to = $_POST['to'];

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
$attendanceRecords = array();

while ($row = $result->fetch_assoc()) {
    $attendanceRecords[] = $row;
}

// Return JSON-encoded data
echo json_encode($attendanceRecords);
?>


