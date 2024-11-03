<?php
include '../db.php'; // Include your database connection file

function getSmsSettings() {
    global $conn;
    $result = mysqli_query($conn, "SELECT * FROM sms_settings WHERE id = 1");
    return mysqli_fetch_assoc($result);
}

function sendSmsNotification($phoneNumbers, $message) {
    $apiToken = "9c5f60c88faf6086782a38bafabd3f9175988cb5"; // Replace with your actual token
    $apiUrl = "https://sms.iprogtech.com/api/v1/sms_messages/send_bulk";

    // Build query string
    $query = http_build_query([
        'phone_number' => $phoneNumbers,
        'message' => $message,
        'api_token' => $apiToken
    ]);

    // Initialize cURL
    $ch = curl_init("$apiUrl?$query");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true); // Decode JSON response
}

function handleRfidTap($rfidUid) {
    global $conn;

    // Get SMS settings
    $smsSettings = getSmsSettings();
    if (!$smsSettings || $smsSettings['notification_status'] != 'enabled') {
        return; // Exit if SMS notifications are disabled
    }

    // Check if the RFID UID exists in the students table
    $studentQuery = mysqli_prepare($conn, "SELECT students.id, students.full_name, students.grade_level_id, students.section_id, students.rfid_uid, parents.phone AS parent_phone 
                                           FROM students 
                                           LEFT JOIN parents ON students.parent_id = parents.parent_id 
                                           WHERE students.rfid_uid = ?");
    mysqli_stmt_bind_param($studentQuery, "s", $rfidUid);
    mysqli_stmt_execute($studentQuery);
    $result = mysqli_stmt_get_result($studentQuery);
    $student = mysqli_fetch_assoc($result);

    if (!$student) {
        echo "RFID UID not recognized.";
        return;
    }

    // Log the event (entry or exit)
    $event = ($smsSettings['notification_event'] == 'entry') ? 'entry' : 'exit';
    $currentTime = date('Y-m-d H:i:s');
    mysqli_query($conn, "INSERT INTO rfid_logs (student_id, event, time) VALUES ('{$student['id']}', '$event', '$currentTime')");

    // Prepare SMS message
    $message = str_replace(
        ['{student_name}', '{entry_time}', '{exit_time}'],
        [$student['full_name'], $currentTime, $currentTime],
        $smsSettings['sms_template']
    );

    // Send SMS if the event matches the configured event (entry, exit, or both)
    if ($smsSettings['notification_event'] == $event || $smsSettings['notification_event'] == 'both') {
        if ($smsSettings['parent_notification'] == 'yes' && !empty($student['parent_phone'])) {
            $response = sendSmsNotification($student['parent_phone'], $message);
            if ($response['status'] == 200) {
                echo "SMS sent to parent successfully!";
            } else {
                echo "Failed to send SMS to parent.";
            }
        }
    }
}

// Example usage
if (isset($_GET['rfid_uid'])) {
    handleRfidTap($_GET['rfid_uid']);
}
?>
