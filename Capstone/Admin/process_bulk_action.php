<?php
include '../db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['lrn_list'])) {
    $action = $_POST['action'];
    $lrn_list = $_POST['lrn_list'];
    $status = $action === 'approve' ? 1 : ($action === 'reject' ? 2 : null);

    if ($status !== null) {
        // Prepare placeholders for the query
        $lrn_placeholders = implode(',', array_fill(0, count($lrn_list), '?'));

        // Fetch original data for comparison
        $sql_fetch = "SELECT lrn, email, grade_level_id, section_id, status FROM students WHERE lrn IN ($lrn_placeholders)";
        $stmt_fetch = $conn->prepare($sql_fetch);
        $stmt_fetch->bind_param(str_repeat('i', count($lrn_list)), ...$lrn_list);
        $stmt_fetch->execute();
        $result = $stmt_fetch->get_result();
        $original_data = [];
        while ($row = $result->fetch_assoc()) {
            $original_data[$row['lrn']] = $row;
        }
        $stmt_fetch->close();

        // Perform the bulk update
        $sql_update = "UPDATE students SET status = ? WHERE lrn IN ($lrn_placeholders)";
        $stmt_update = $conn->prepare($sql_update);
        $params = array_merge([$status], $lrn_list);
        $stmt_update->bind_param(str_repeat('i', count($params)), ...$params);

        if ($stmt_update->execute()) {
            // Check for changes and send emails
            foreach ($lrn_list as $lrn) {
                $original = $original_data[$lrn];
                $email = $original['email'];
                
                // Fetch the updated data
                $sql_updated = "SELECT grade_level_id, section_id FROM students WHERE lrn = ?";
                $stmt_updated = $conn->prepare($sql_updated);
                $stmt_updated->bind_param("i", $lrn);
                $stmt_updated->execute();
                $updated = $stmt_updated->get_result()->fetch_assoc();
                $stmt_updated->close();

                // Determine changes
                $changes = [];
                if ($original['grade_level_id'] != $updated['grade_level_id']) {
                    $changes[] = "Grade Level changed.";
                }
                if ($original['section_id'] != $updated['section_id']) {
                    $changes[] = "Section changed.";
                }

                // Send email if there are changes
                if (!empty($changes)) {
                    $to = $email;
                    $subject = "Profile Update Notification";
                    $message = "Dear Student,\n\nYour profile has been updated:\n";
                    $message .= implode("\n", $changes) . "\n\n";
                    $message .= "Please contact the administration for more information.\n\nBest regards,\nSchool Administration";
                    $headers = "From: admin@school.com";

                    mail($to, $subject, $message, $headers);
                }
            }

            echo json_encode(['status' => 'success', 'message' => ucfirst($action) . ' action applied successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error applying bulk action.']);
        }

        $stmt_update->close();
    } elseif ($action === 'archive') {
        $lrn_placeholders = implode(',', array_fill(0, count($lrn_list), '?'));
        $sql_archive = "UPDATE students SET is_archived = 1 WHERE lrn IN ($lrn_placeholders)";
        $stmt_archive = $conn->prepare($sql_archive);
        $stmt_archive->bind_param(str_repeat('i', count($lrn_list)), ...$lrn_list);

        if ($stmt_archive->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Archive action applied successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error archiving students.']);
        }

        $stmt_archive->close();
    }
}
?>
