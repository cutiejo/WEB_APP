<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['teacher_id']) && !empty($_POST['teacher_id'])) {
        $teacher_id = intval($_POST['teacher_id']);  // Sanitize the input

        // Fetch the teacher and their grade and section assignments
        $teacher = $conn->query("
            SELECT t.*, 
            IFNULL(GROUP_CONCAT(DISTINCT g.grade_level, ' - ', s.section SEPARATOR ', '), 'No assignments') AS grade_section
            FROM teachers t
            LEFT JOIN teacher_assignments ta ON t.id = ta.teacher_id
            LEFT JOIN grade_levels g ON ta.grade_level_id = g.id
            LEFT JOIN sections s ON ta.section_id = s.id
            WHERE t.id = $teacher_id
            GROUP BY t.id
        ")->fetch_assoc();

        if ($teacher) {
            // Prepare the response
            $response = [
                'teacher' => $teacher,
                'assigned_grade_levels' => explode(', ', $teacher['grade_section']),
                'assigned_sections' => explode(', ', $teacher['grade_section'])
            ];
            echo json_encode($response);
        } else {
            echo json_encode(['error' => 'Teacher not found.']);
        }
    } else {
        echo json_encode(['error' => 'No teacher ID provided.']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method.']);
}
?>
