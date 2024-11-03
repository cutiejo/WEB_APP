<?php
function logAttendance($user_id, $rfid_uid, $full_name, $grade_level_id, $section_id) {
    include '../db.php';

    // Time-based approach for entry/exit
    $current_time = date('H:i:s');
    $scan_time = date('Y-m-d H:i:s');

    // Define time ranges (adjust times as needed)
    $morning_start = '07:00:00';
    $morning_end = '09:00:00';
    $afternoon_start = '15:00:00';
    $afternoon_end = '17:00:00';

    if ($current_time >= $morning_start && $current_time <= $morning_end) {
        $event_type = 'Entry';
        $status = 'Present';
    } elseif ($current_time >= $afternoon_start && $current_time <= $afternoon_end) {
        $event_type = 'Exit';
        $status = 'Present';
    } else {
        $event_type = 'Late';
        $status = 'Late';
    }

    // Check for duplicate attendance entries
    $duplicate_query = "SELECT * FROM attendance WHERE user_id = ? AND event_type = ? AND DATE(scan_time) = CURDATE()";
    $duplicate_stmt = $conn->prepare($duplicate_query);
    $duplicate_stmt->bind_param("is", $user_id, $event_type);
    $duplicate_stmt->execute();
    $duplicate_result = $duplicate_stmt->get_result();

    if ($duplicate_result->num_rows == 0) {
        // Log attendance
        $attendance_query = "INSERT INTO attendance (user_id, rfid_uid, full_name, grade_level, section, scan_time, event_type, status) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $attendance_stmt = $conn->prepare($attendance_query);
        $attendance_stmt->bind_param("isssssss", $user_id, $rfid_uid, $full_name, $grade_level_id, $section_id, $scan_time, $event_type, $status);

        if ($attendance_stmt->execute()) {
            return [
                'status' => 'success',
                'message' => 'Attendance logged successfully',
                'scan_time' => $scan_time,
                'event_type' => $event_type
            ];
        } else {
            return ['status' => 'error', 'message' => 'Failed to log attendance'];
        }
    } else {
        return ['status' => 'error', 'message' => 'Attendance already logged for today'];
    }
}
