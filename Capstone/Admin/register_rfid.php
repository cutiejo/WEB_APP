<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_type = mysqli_real_escape_string($conn, $_POST['user_type']);  // "student" or "teacher"
    $lrn = mysqli_real_escape_string($conn, $_POST['lrn']);
    $rfid_uid = mysqli_real_escape_string($conn, $_POST['rfid_uid']);

    // Set table based on user type
    $table = ($user_type == 'student') ? 'students' : 'teachers';
    $id_column = ($user_type == 'student') ? 'lrn' : 'employee_id';  // Assuming teachers have 'employee_id'

    // Check if RFID UID already exists in either students or teachers table
    $check_query_students = "SELECT * FROM students WHERE rfid_uid = '$rfid_uid'";
    $check_query_teachers = "SELECT * FROM teachers WHERE rfid_uid = '$rfid_uid'";

    $check_result_students = mysqli_query($conn, $check_query_students);
    $check_result_teachers = mysqli_query($conn, $check_query_teachers);

    if (mysqli_num_rows($check_result_students) > 0 || mysqli_num_rows($check_result_teachers) > 0) {
        // RFID UID already exists for another user
        header("Location: settings.php?success=rfid_exists&active_panel=rfidDigit");
    } else {
        // Register RFID UID for the user in the selected table
        $update_query = "UPDATE $table SET rfid_uid = '$rfid_uid' WHERE $id_column = '$lrn'";
        if (mysqli_query($conn, $update_query)) {
            // Success, RFID UID registered
            header("Location: settings.php?success=rfid_registered&active_panel=rfidDigit");
        } else {
            // Failure, return an error
            header("Location: settings.php?success=rfid_failed&active_panel=rfidDigit");
        }
    }
}
?>
