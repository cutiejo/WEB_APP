<?php
// Sample code to fetch the teacher's current assignments and available options
include '../db.php';

$teacher_id = $_POST['teacher_id'];

// Fetch current assignments
$current_assignments = $conn->query("
    SELECT g.id AS grade_level_id, g.grade_level, s.id AS section_id, s.section
    FROM teacher_assignments ta
    LEFT JOIN grade_levels g ON ta.grade_level_id = g.id
    LEFT JOIN sections s ON ta.section_id = s.id
    WHERE ta.teacher_id = $teacher_id
");

// Fetch available grade levels
$grade_levels = $conn->query("SELECT * FROM grade_levels");

// Fetch available sections
$sections = $conn->query("SELECT * FROM sections");

$response = [
    'current_assignments' => $current_assignments->fetch_all(MYSQLI_ASSOC),
    'grade_levels' => $grade_levels->fetch_all(MYSQLI_ASSOC),
    'sections' => $sections->fetch_all(MYSQLI_ASSOC)
];

echo json_encode($response);
?>
