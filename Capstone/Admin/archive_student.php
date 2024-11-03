<?php
include '../db.php';

if (isset($_POST['ids'])) {
    $ids = $_POST['ids'];

    foreach ($ids as $lrn) {
        $conn->query("UPDATE students SET is_archived = 1 WHERE lrn = '$lrn'");
    }

    echo json_encode(['status' => 'success', 'message' => 'Selected students archived successfully']);
}
?>
