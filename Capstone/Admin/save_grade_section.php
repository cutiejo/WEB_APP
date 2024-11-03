<?php
include '../db.php'; // Adjust path as necessary

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = $_POST['teacher_id'];
    $grade_level_id = $_POST['grade_level_id'];  // Match with your form fields
    $section_id = $_POST['section_id'];          // Match with your form fields

    // Insert into teacher_assignments table
    $insertAssignment = $conn->prepare("INSERT INTO teacher_assignments (teacher_id, grade_level_id, section_id) VALUES (?, ?, ?)");
    $insertAssignment->bind_param("iii", $teacher_id, $grade_level_id, $section_id);
    
    if ($insertAssignment->execute()) {
        // If the insertion into `teacher_assignments` succeeds, update the `teachers` table
        $updateTeacher = $conn->prepare("UPDATE teachers SET grade_level_id = ?, section_id = ? WHERE id = ?");
        $updateTeacher->bind_param("iii", $grade_level_id, $section_id, $teacher_id);
        
        if ($updateTeacher->execute()) {
            // Successful update
            echo json_encode(['status' => 'success', 'message' => 'Assignment successful and teacher updated!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update teacher.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to assign grade/section.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
