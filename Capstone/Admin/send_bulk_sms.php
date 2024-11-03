<?php
session_start();
include '../db.php'; // Database connection

// Redirect if not an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../Login/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message = $_POST['sms_message'];
    $recipient_group = $_POST['recipient_group'];
    $phone_numbers = [];

    // Fetch phone numbers based on recipient group
    if ($recipient_group == 'all_students') {
        $query = "SELECT phone FROM students WHERE phone IS NOT NULL";
    } elseif ($recipient_group == 'all_teachers') {
        $query = "SELECT phone FROM teachers WHERE phone IS NOT NULL";
    } elseif ($recipient_group == 'all_parents') {
        $query = "SELECT phone FROM parents WHERE phone IS NOT NULL";
    }

    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $phone_numbers[] = $row['phone'];
    }

    if (empty($phone_numbers)) {
        // Redirect with an error if no phone numbers were found
        header("Location: settings.php?active_panel=smsBlaster&success=sms_failed");
        exit();
    }

    // Sending SMS using API
    $api_token = "9c5f60c88faf6086782a38bafabd3f9175988cb5";
    $api_url = "https://sms.iprogtech.com/api/v1/sms_messages/send_bulk";

    // Iterate through phone numbers and send each SMS individually
    foreach ($phone_numbers as $phone) {
        // Log the message as pending in the database
        $log_id = logMessage($conn, $phone, $message);

        // Prepare data for POST
        $postData = [
            'phone_number' => $phone,
            'message' => $message,
            'api_token' => $api_token
        ];

        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);

        // Handle cURL errors
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            updateMessageStatus($conn, $log_id, 'failed', $error_msg);
        } else {
            // Decode API response and update status
            $response_data = json_decode($response, true);
            if (isset($response_data['status']) && $response_data['status'] == 200) {
                updateMessageStatus($conn, $log_id, 'sent', $response);
            } else {
                updateMessageStatus($conn, $log_id, 'failed', json_encode($response_data));
            }
        }
        curl_close($ch);
    }

    // Redirect with success message after bulk send attempt
    header("Location: settings.php?active_panel=smsBlaster&success=sms_sent");
    exit();
}

// Function to log SMS in database
function logMessage($conn, $phone, $message) {
    $stmt = $conn->prepare("INSERT INTO message_logs (recipient_phone, message, status) VALUES (?, ?, 'pending')");
    $stmt->bind_param("ss", $phone, $message);
    $stmt->execute();
    $log_id = $stmt->insert_id;
    $stmt->close();
    return $log_id;
}

// Function to update message status
function updateMessageStatus($conn, $log_id, $status, $response) {
    $stmt = $conn->prepare("UPDATE message_logs SET status = ?, response = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $response, $log_id);
    $stmt->execute();
    $stmt->close();
}
?>
