<?php
// api/get_attendance.php

// Include database connection
include '../db.php';

// Check if user_id is provided
if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
    echo json_encode([
        "status" => false,
        "message" => "User ID not provided."
    ]);
    exit();
}

// Sanitize the user_id to prevent SQL injection
$user_id = mysqli_real_escape_string($conn, $_GET['user_id']);

// Query to fetch attendance data for the specified user_id
$query = "
    SELECT id, date, status
    FROM attendance
    WHERE user_id = '$user_id'
    ORDER BY date DESC
";

$result = mysqli_query($conn, $query);

// Check if data is found
if (mysqli_num_rows($result) > 0) {
    $attendanceData = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $attendanceData[] = [
            "id" => $row['id'],
            "date" => $row['date'],
            "status" => $row['status']
        ];
    }

    echo json_encode([
        "status" => true,
        "data" => $attendanceData
    ]);
} else {
    echo json_encode([
        "status" => false,
        "message" => "No attendance data found for this user."
    ]);
}

// Close database connection
mysqli_close($conn);
?>
