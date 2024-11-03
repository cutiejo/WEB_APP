<?php
include '../db.php';

if (isset($_POST['teacher_id'])) {
    $teacher_id = $_POST['teacher_id'];

    // Fetch teacher's data
    $query = "
        SELECT 
            t.*, 
            GROUP_CONCAT(ta.grade_level_id) AS assigned_grades, 
            GROUP_CONCAT(ta.section_id) AS assigned_sections
        FROM teachers t
        LEFT JOIN teacher_assignments ta ON t.id = ta.teacher_id
        WHERE t.id = ?
        GROUP BY t.id";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $teacher = $result->fetch_assoc();

    // Fetch grade levels
    $grade_levels_query = "SELECT id, grade_level FROM grade_levels";
    $grade_levels_result = $conn->query($grade_levels_query);
    $grade_levels = $grade_levels_result->fetch_all(MYSQLI_ASSOC);

    // Fetch sections
    $sections_query = "SELECT id, section FROM sections";
    $sections_result = $conn->query($sections_query);
    $sections = $sections_result->fetch_all(MYSQLI_ASSOC);

    // Return JSON response
    echo json_encode([
        'teacher' => $teacher,
        'grade_levels' => $grade_levels,
        'sections' => $sections
    ]);
}
?>
