<?php
include '../db.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $teacher_id = $_POST['teacher_id'];
    $grade_levels = $_POST['grade_level_ids'];
    $sections = $_POST['section_ids'];

    $error_message = '';
    
    foreach ($grade_levels as $key => $grade_level) {
        $section_id = $sections[$key];

        // Check if this grade level and section combination already exists for the teacher
        $check_existing = $conn->prepare("SELECT * FROM teacher_assignments WHERE teacher_id = ? AND grade_level_id = ? AND section_id = ?");
        $check_existing->bind_param("iii", $teacher_id, $grade_level, $section_id);
        $check_existing->execute();
        $existing_result = $check_existing->get_result();

        if ($existing_result->num_rows > 0) {
            $error_message = 'This grade level and section is already assigned!';
            break;
        } else {
            // Assign the new grade level and section
            $assign = $conn->prepare("INSERT INTO teacher_assignments (teacher_id, grade_level_id, section_id) VALUES (?, ?, ?)");
            $assign->bind_param("iii", $teacher_id, $grade_level, $section_id);
            $assign->execute();
        }
    }

    if ($error_message != '') {
        echo json_encode(['status' => 'error', 'message' => $error_message]);
    } else {
        echo json_encode(['status' => 'success']);
    }
}

?>
