<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $grade_level_name = mysqli_real_escape_string($conn, $_POST['grade_level_name']);
    
    $query = "UPDATE grade_levels SET grade_level = '$grade_level_name' WHERE id = '$id'";
    
    if (mysqli_query($conn, $query)) {
        header("Location: settings.php?success=grade_level_updated&active_panel=classManagement");
    } else {
        header("Location: settings.php?success=grade_level_update_failed&active_panel=classManagement");
    }
}
?>
