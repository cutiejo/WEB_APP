<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    
    $query = "DELETE FROM grade_levels WHERE id = '$id'";
    
    if (mysqli_query($conn, $query)) {
        header("Location: settings.php?success=grade_level_deleted&active_panel=classManagement");
    } else {
        header("Location: settings.php?success=grade_level_delete_failed&active_panel=classManagement");
    }
}
?>
