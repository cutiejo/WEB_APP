<?php
include '../db.php';

function sendSmsNotification($user_id, $full_name, $event_type) {
    global $conn;

    // Retrieve the parent's phone number from the database based on student ID
    $query = "SELECT phone FROM parents WHERE student_id = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        return ['status' => 'error', 'message' => 'Failed to prepare query for retrieving parent phone'];
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $parent = $result->fetch_assoc();
        $phone = $parent['phone'];

        // Construct the message for SMS notification
        $message = "Hello, your child $full_name has $event_type the school at " . date('H:i:s');

        // Call the SMS sending function (assuming a function like sendSms exists)
        if (sendSms($phone, $message)) { // Replace with actual SMS sending function if different
            return ['status' => 'success', 'message' => 'SMS sent successfully'];
        } else {
            return ['status' => 'error', 'message' => 'Failed to send SMS'];
        }
    } else {
        return ['status' => 'error', 'message' => 'Parent phone number not found'];
    }

    $stmt->close();
}

// Mockup of the sendSms function (replace this with actual SMS sending logic)
function sendSms($phone, $message) {
    // Example SMS sending logic here
    // Integrate with your SMS provider API
    // Return true if SMS is sent successfully, false otherwise

    // For now, let's simulate it with a success response
    return true;
}
?>
