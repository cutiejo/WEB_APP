<?php
include '../db.php'; // Include your database connection

// Function to retrieve user data based on RFID
function get_user_data($rfid_uid, $conn) {
    // Query to fetch the data
    $query = "SELECT u.*, g.grade_level, s.section 
              FROM users u
              LEFT JOIN students st ON u.id = st.user_id
              LEFT JOIN grade_levels g ON st.grade_level_id = g.id
              LEFT JOIN sections s ON st.section_id = s.id
              WHERE u.rfid_uid = '$rfid_uid' AND u.status = 1"; // Assuming status = 1 means active
              
    $result = mysqli_query($conn, $query);
    
    return mysqli_fetch_assoc($result);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rfid_uid = $_POST['rfid_uid']; // Assuming RFID UID is sent via POST

    // Validate and sanitize the RFID UID
    $rfid_uid = mysqli_real_escape_string($conn, $rfid_uid);

    // Fetch user data
    $user = get_user_data($rfid_uid, $conn);

    if ($user) {
        // Prepare the response
        $response = [
            'status' => 'success',
            'data' => [
                'full_name' => $user['full_name'],
                'role' => $user['role'],
                'rfid_uid' => $user['rfid_uid'],
                'grade_level' => isset($user['grade_level']) ? $user['grade_level'] : null,
                'section' => isset($user['section']) ? $user['section'] : null,
                'image' => $user['image'], // Assuming this is a path to the image
                'scan_time' => date('Y-m-d H:i:s')
            ]
        ];
    } else {
        // No user found with this RFID UID
        $response = [
            'status' => 'error',
            'message' => 'No user found with this RFID UID or user is not approved.'
        ];
    }
} else {
    // Invalid request method
    $response = [
        'status' => 'error',
        'message' => 'Invalid request method.'
    ];
}

// Return the response as JSON
header('Content-Type: application/json');
echo json_encode($response);

// Close the database connection
mysqli_close($conn);
?>
