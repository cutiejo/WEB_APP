<?php
include '../db.php';

if (isset($_POST['student_id'])) {
    $student_id = $_POST['student_id'];
    $query = $conn->prepare("
        SELECT s.*, 
               IFNULL(g.grade_level, 'No Grade Assigned') AS grade_level, 
               IFNULL(sec.section, 'No Section Assigned') AS section,
               CASE 
                   WHEN s.status = 0 THEN 'Pending' 
                   WHEN s.status = 1 THEN 'Approved' 
                   ELSE 'Unknown' 
               END AS status
        FROM students s
        LEFT JOIN grade_levels g ON s.grade_level_id = g.id
        LEFT JOIN sections sec ON s.section_id = sec.id
        WHERE s.lrn = ?
    ");
    
    $query->bind_param("s", $student_id);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        // Set image path, default to avatar if no image found or file doesn't exist
        $student['image'] = !empty($student['image']) && file_exists('../' . $student['image']) 
                            ? '../' . $student['image'] 
                            : '../uploads/default-avatar.png'; 
        echo json_encode($student);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Student not found']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>
