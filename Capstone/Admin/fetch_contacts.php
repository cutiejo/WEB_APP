<?php
require_once '../db.php';

$sql = "SELECT user_id, full_name, role, image FROM users WHERE role IN ('teacher', 'student', 'parent')";
$result = $conn->query($sql);

$contacts = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $contacts[] = $row;
    }
} else {
    echo json_encode(['status' => false, 'message' => 'No contacts found']);
    exit;
}

echo json_encode(['status' => true, 'contacts' => $contacts]);
?>
