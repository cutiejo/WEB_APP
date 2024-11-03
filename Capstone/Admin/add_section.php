<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section = mysqli_real_escape_string($conn, $_POST['section']);

    // Check if the section already exists
    $check_query = "SELECT * FROM sections WHERE section = '$section'";
    $result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($result) == 0) {
        $query = "INSERT INTO sections (section) VALUES ('$section')";
        if (mysqli_query($conn, $query)) {
            header("Location: settings.php?panel=classManagement&success=section_added");
            exit();
        } else {
            echo "Error: " . $query . "<br>" . mysqli_error($conn);
        }
    } else {
        header("Location: settings.php?panel=classManagement&error=section_exists");
        exit();
    }
}
?>
