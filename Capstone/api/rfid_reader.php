<?php
include '../db.php';
include 'attendance_logger.php';
include 'sms_notifier.php';

header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit();
}

// Decode JSON input
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['rfid_uid'])) {
    echo json_encode(['status' => 'error', 'message' => 'RFID UID missing']);
    exit();
}

$rfid_uid = mysqli_real_escape_string($conn, $data['rfid_uid']);

// Prepare the query to check if the RFID UID exists in the students table
$student_query = "SELECT * FROM students WHERE rfid_uid = ? AND status = 1";
$stmt = $conn->prepare($student_query);

if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Database query preparation failed']);
    exit();
}

$stmt->bind_param("s", $rfid_uid);

if (!$stmt->execute()) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to execute query']);
    $stmt->close();
    mysqli_close($conn);
    exit();
}

$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $student = $result->fetch_assoc();
    $user_id = $student['user_id'];
    $full_name = $student['full_name'];
    $grade_level_id = $student['grade_level_id'];
    $section_id = $student['section_id'];

    // Call the attendance logging function
    $attendance_result = logAttendance($user_id, $rfid_uid, $full_name, $grade_level_id, $section_id);

    if ($attendance_result && $attendance_result['status'] === 'success') {
        // Send SMS notification if attendance is logged successfully
        if (sendSmsNotification($user_id, $full_name, $attendance_result['event_type'])) {
            $response = [
                'status' => 'success',
                'message' => $attendance_result['message'],
                'scan_time' => $attendance_result['scan_time']
            ];
        } else {
            $response = [
                'status' => 'warning',
                'message' => 'Attendance logged, but SMS notification failed',
                'scan_time' => $attendance_result['scan_time']
            ];
        }
    } else {
        $response = [
            'status' => 'error',
            'message' => $attendance_result['message'] ?? 'Failed to log attendance'
        ];
    }
} else {
    // If the RFID is not found or the student is not approved
    $response = [
        'status' => 'error',
        'message' => 'RFID not found or student not approved.'
    ];
}

// Close prepared statement and database connection
$stmt->close();
mysqli_close($conn);

echo json_encode($response);
