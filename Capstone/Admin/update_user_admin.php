<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    if (!empty($id) && !empty($full_name) && !empty($email) && !empty($role)) {
        // Prepare and execute the SQL query to update the user
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, role = ? WHERE id = ?");
        $stmt->bind_param("sssi", $full_name, $email, $role, $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'User updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update user']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

$conn->close();
?>
