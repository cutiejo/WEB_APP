<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $grade_level_name = mysqli_real_escape_string($conn, $_POST['grade_level_name']);

    $query = "INSERT INTO grade_levels (grade_level) VALUES ('$grade_level_name')";
    
    if (mysqli_query($conn, $query)) {
        header("Location: settings.php?panel=classManagement&success=grade_level_added");
        exit();
    } else {
        echo "Error: " . $query . "<br>" . mysqli_error($conn);
    }
}
?>
