<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $section_name = mysqli_real_escape_string($conn, $_POST['section_name']);
    
    $query = "UPDATE sections SET section = '$section_name' WHERE id = '$id'";
    
    if (mysqli_query($conn, $query)) {
        header("Location: settings.php?success=section_updated&active_panel=classManagement");
    } else {
        header("Location: settings.php?success=section_update_failed&active_panel=classManagement");
    }
}
?>
