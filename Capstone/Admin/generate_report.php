<?php
include '../db.php';

// Headers to ensure it downloads as a CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=report.csv');

// Create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// Output the column headings
fputcsv($output, array('Report Type', 'Count'));

// Fetch total number of students
$result = $conn->query("SELECT COUNT(id) as total_students FROM students");
$total_students = $result->fetch_assoc()['total_students'];

// Fetch total number of teachers
$result = $conn->query("SELECT COUNT(id) as total_teachers FROM teachers");
$total_teachers = $result->fetch_assoc()['total_teachers'];

// Fetch total number of users
$result = $conn->query("SELECT COUNT(id) as total_users FROM users");
$total_users = $result->fetch_assoc()['total_users'];

// Output the data rows
fputcsv($output, array('Total Students', $total_students));
fputcsv($output, array('Total Teachers', $total_teachers));
fputcsv($output, array('Total Users', $total_users));

// Close the output stream
fclose($output);
?>