<?php
include '../db.php';

if (isset($_POST['grade_level_ids'])) {
    $grade_level_ids = implode(',', $_POST['grade_level_ids']);
    $query = "
        SELECT s.id, s.section 
        FROM grade_level_section gs
        JOIN sections s ON gs.section_id = s.id
        WHERE gs.grade_level_id IN ($grade_level_ids)";

    $result = $conn->query($query);

    $sections = [];
    while ($row = $result->fetch_assoc()) {
        $sections[] = $row;
    }

    echo json_encode(['sections' => $sections]);
}
?>
