<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['backup_file']) && $_FILES['backup_file']['error'] == UPLOAD_ERR_OK) {
    $backup_file = $_FILES['backup_file']['tmp_name'];

    // Read the file content
    $sql_content = file_get_contents($backup_file);

    // Split the SQL file into individual queries
    $sql_queries = explode(';', $sql_content);

    $conn->begin_transaction();

    try {
        foreach ($sql_queries as $query) {
            $trimmed_query = trim($query);
            if (!empty($trimmed_query)) {
                $conn->query($trimmed_query);
            }
        }
        $conn->commit();
        header("Location: settings.php?active_panel=backupRestore&success=database_restored");
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: settings.php?active_panel=backupRestore&error=restore_failed");
    }
} else {
    header("Location: settings.php?active_panel=backupRestore&error=file_upload_failed");
}
