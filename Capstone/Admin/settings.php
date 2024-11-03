<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../Login/login.php"); // Redirect to login if not an admin
    exit();
}
include '../db.php';

// Handling success messages
$success_message = "";
$active_panel = isset($_GET['active_panel']) ? $_GET['active_panel'] : "smsNotification"; // Default active panel
$alert_class = "alert-success"; // Default to success alert
if (isset($_GET['success'])) {
    if ($_GET['success'] == "section_added") {
        $success_message = "Section added successfully!";
        $active_panel = "classManagement";
    } elseif ($_GET['success'] == "grade_level_added") {
        $success_message = "Grade level added successfully!";
        $active_panel = "classManagement";
    } elseif ($_GET['success'] == "section_updated") {
        $success_message = "Section updated successfully!";
        $active_panel = "classManagement";
    } elseif ($_GET['success'] == "section_deleted") {
        $success_message = "Section deleted successfully!";
        $active_panel = "classManagement";
    } elseif ($_GET['success'] == "grade_level_updated") {
        $success_message = "Grade level updated successfully!";
        $active_panel = "classManagement";
    } elseif ($_GET['success'] == "grade_level_deleted") {
        $success_message = "Grade level deleted successfully!";
        $active_panel = "classManagement";
    } elseif ($_GET['success'] == "rfid_registered") {
        $success_message = "RFID tag registered successfully!";
        $active_panel = "rfidDigit";
    } elseif ($_GET['success'] == "rfid_exists") {
        $success_message = "This RFID tag is already registered to another student.";
        $active_panel = "rfidDigit";
        $alert_class = "alert-danger"; // Error styling
    } elseif ($_GET['success'] == "rfid_failed") {
        $success_message = "Failed to register RFID tag. Please try again.";
        $active_panel = "rfidDigit";
        $alert_class = "alert-danger"; // Error styling
    } elseif ($_GET['success'] == "sms_sent") {
        $success_message = "Bulk SMS sent successfully!";
        $active_panel = "smsBlaster";
    } elseif ($_GET['success'] == "sms_failed") {
        $success_message = "Failed to send SMS. Please try again.";
        $active_panel = "smsBlaster";
        $alert_class = "alert-danger"; // Error styling for SMS failure
    } elseif ($_GET['success'] == "sms_settings_saved") {
        $success_message = "SMS Notification Settings saved successfully!";
        $active_panel = "smsNotification";
    }
    
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="styles.css">
    <style>
        /*for content*/
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
        }

        .container-fluid {
            display: flex;
            height: calc(100vh - 80px);
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .content-sidebar {
            width: 60px;
            background-color: #fff;
            padding: 10px;
            border-right: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
            transition: width 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .content-sidebar ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
            width: 100%;
            text-align: center;
        }

        .content-sidebar ul li {
            margin-bottom: 20px;
            width: 100%;
        }

        .content-sidebar ul li a {
            text-decoration: none;
            color: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            width: 100%;
        }

        .content-sidebar ul li a i {
            font-size: 1.5rem;
        }

        .content-sidebar ul li a.active,
        .content-sidebar ul li a:hover {
            font-weight: bold;
            background-color: #218838;
            color: #fff;
        }

        .main-content1 {
            flex-grow: 1;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
            transition: margin-left 0.3s ease;
            margin-left: 10px;
        }

        @media (max-width: 768px) {
            .settings-container {
                flex-direction: column;
            }
            .content-sidebar {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid #ddd;
                margin-bottom: 10px;
            }
            .main-content1 {
                margin-left: 0;
                padding: 15px;
            }
        }

        @media (max-width: 480px) {
            .content-sidebar ul li a i {
                font-size: 1.2rem;
            }
            .content-sidebar ul li {
                margin-bottom: 15px;
            }
            .main-content1 {
                padding: 10px;
            }
        }

        .settings-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }

        .settings-header h3 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
        }

        .settings-panel {
            display: none;
        }

        .settings-panel.active {
            display: block;
        }

        /* Custom CSS for Class Management */
        .class-management-wrapper {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            gap: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .class-management-section,
        .class-management-grade {
            margin-left: 20px;
            flex: 1;
            min-width: 300px;
        }
        /* Custom Alert Styling */
    .alert-success {
        max-width: 400px; /* Limit the width of the alert */
        margin: 10px auto; /* Center the alert horizontally */
        padding: 8px 15px; /* Adjust padding for a more compact look */
        font-size: 14px; /* Smaller font size */
        text-align: center; /* Center the text */
        border-radius: 5px; /* Slightly rounded corners */
    }

    /* Custom Table Row Styling */
    .table-bordered tbody tr {
        height: 30px; /* Reduce the height of the table rows */
        font-size: 14px; /* Adjust font size for better readability with smaller rows */
        padding: 5px 10px; /* Smaller padding inside the table cells */
    }

    .table-bordered td, .table-bordered th {
        padding: 5px 10px; /* Smaller padding inside the table cells */
    }

    /* Backup and Restore Panel Styles */
.backup-restore-wrapper {
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 10px;
    box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
}

.backup-restore-wrapper h5 {
    margin-top: 0;
    font-size: 1.25rem;
    font-weight: bold;
    color: #333;
}

.backup-restore-wrapper form {
    margin-bottom: 20px;
}

.backup-restore-wrapper .btn {
    width: 12%;
    padding: 10px;
    font-size: 1rem;
}

/* SMS Blaster Panel Styles */
.sms-blaster-wrapper {
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 10px;
    box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
}

.sms-blaster-wrapper h5 {
    margin-top: 0;
    font-size: 1.25rem;
    font-weight: bold;
    color: #333;
}

.sms-blaster-wrapper form .form-label {
    font-size: 1rem;
    color: #333;
}

.sms-blaster-wrapper form textarea {
    font-size: 1rem;
    padding: 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
}

.sms-blaster-wrapper form .form-select {
    font-size: 1rem;
    padding: 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
}

.sms-blaster-wrapper form .btn {
    width: 10%;
    padding: 10px;
    font-size: 1rem;
}

/* SMS Notification Panel Styles */
.sms-notification-wrapper {
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 10px;
    box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
}

.sms-notification-wrapper h5 {
    margin-top: 0;
    font-size: 1.25rem;
    font-weight: bold;
    color: #333;
}

.sms-notification-wrapper form .form-label {
    font-size: 1rem;
    color: #333;
}

.sms-notification-wrapper form .form-control, 
.sms-notification-wrapper form .form-select {
    font-size: 1rem;
    padding: 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
}

.sms-notification-wrapper form .btn {
    width: 12%;
    padding: 10px;
    font-size: 1rem;
}

/* RFID Digit Settings Panel Styles */
.rfid-digit-wrapper {
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 10px;
    box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
}

.rfid-digit-wrapper h5 {
    margin-top: 0;
    font-size: 1.25rem;
    font-weight: bold;
    color: #333;
}

.rfid-digit-wrapper form .form-label {
    font-size: 1rem;
    color: #333;
}

.rfid-digit-wrapper form .form-control {
    font-size: 1rem;
    padding: 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
}

.rfid-digit-wrapper form .btn {
    width: 12%;
    padding: 10px;
    font-size: 1rem;
}



    </style>
<body>
    <!-- Navbar -->
    <?php include 'navbar.php'; ?>

    <!-- Main Content -->
    <div class="main-content" id="main-content">
        <div class="container-fluid">
            <!-- Content Sidebar -->
            <div class="content-sidebar" id="sidebar">
                <ul>
                    <li><a href="#" data-panel="smsNotification" class="<?php echo ($active_panel == 'smsNotification') ? 'active' : ''; ?>"><i class="fa-solid fa-message"></i></a></li>
                    <li><a href="#" data-panel="rfidDigit" class="<?php echo ($active_panel == 'rfidDigit') ? 'active' : ''; ?>"><i class="fas fa-sliders-h"></i></a></li>
                    <li><a href="#" data-panel="classManagement" class="<?php echo ($active_panel == 'classManagement') ? 'active' : ''; ?>"><i class="fas fa-sitemap"></i></a></li>
                    <li><a href="#" data-panel="backupRestore" class="<?php echo ($active_panel == 'backupRestore') ? 'active' : ''; ?>"><i class="fas fa-cloud-upload-alt"></i></a></li>
                    <li><a href="#" data-panel="smsBlaster" class="<?php echo ($active_panel == 'smsBlaster') ? 'active' : ''; ?>"><i class="fas fa-sms"></i></a></li>
                </ul>
            </div>

            <!-- Main Content Area -->
            <div class="main-content1" id="mainContent">
                <div class="settings-header">
                    <h3>Settings</h3>
                </div>
                
                <div class="settings-panel <?php echo ($active_panel == 'smsNotification') ? 'active' : ''; ?>" id="smsNotification">
    <h4>SMS Notification Settings</h4>
    <div class="sms-notification-wrapper">
    <?php if (!empty($success_message)) : ?>
    <div class="alert <?php echo $alert_class; ?>" id="successAlert">
        <?php echo $success_message; ?>
    </div>
<?php endif; ?>

        <h5>Configure SMS Notifications</h5>
        <form action="save_sms_settings.php" method="POST">
            <div class="mb-3">
                <label for="sender_name" class="form-label">Sender Name</label>
                <input type="text" name="sender_name" id="sender_name" class="form-control" placeholder="Enter sender name for SMS" value="<?php echo isset($sms_settings['sender_name']) ? htmlspecialchars($sms_settings['sender_name']) : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="notification_status" class="form-label">Enable Notifications</label>
                <select name="notification_status" id="notification_status" class="form-select" required>
                    <option value="enabled" <?php echo isset($sms_settings['notification_status']) && $sms_settings['notification_status'] == 'enabled' ? 'selected' : ''; ?>>Enabled</option>
                    <option value="disabled" <?php echo isset($sms_settings['notification_status']) && $sms_settings['notification_status'] == 'disabled' ? 'selected' : ''; ?>>Disabled</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="notification_event" class="form-label">Select Notification Events</label>
                <select name="notification_event" id="notification_event" class="form-select" required>
                    <option value="entry" <?php echo isset($sms_settings['notification_event']) && $sms_settings['notification_event'] == 'entry' ? 'selected' : ''; ?>>Student Entry</option>
                    <option value="exit" <?php echo isset($sms_settings['notification_event']) && $sms_settings['notification_event'] == 'exit' ? 'selected' : ''; ?>>Student Exit</option>
                    <option value="both" <?php echo isset($sms_settings['notification_event']) && $sms_settings['notification_event'] == 'both' ? 'selected' : ''; ?>>Both Entry and Exit</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="parent_notification" class="form-label">Notify Parents</label>
                <select name="parent_notification" id="parent_notification" class="form-select" required>
                    <option value="yes" <?php echo isset($sms_settings['parent_notification']) && $sms_settings['parent_notification'] == 'yes' ? 'selected' : ''; ?>>Yes</option>
                    <option value="no" <?php echo isset($sms_settings['parent_notification']) && $sms_settings['parent_notification'] == 'no' ? 'selected' : ''; ?>>No</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="sms_template" class="form-label">SMS Template</label>
                <textarea name="sms_template" id="sms_template" class="form-control" rows="4" placeholder="Enter your SMS template here" required><?php echo isset($sms_settings['sms_template']) ? htmlspecialchars($sms_settings['sms_template']) : ''; ?></textarea>
                <small class="form-text text-muted">You can use placeholders like <code>{student_name}</code>, <code>{entry_time}</code>, <code>{exit_time}</code>, etc.</small>
            </div>
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
    </div>
</div>

            <!--RFID Digit Settings-->
            <div class="settings-panel <?php echo ($active_panel == 'rfidDigit') ? 'active' : ''; ?>" id="rfidDigit">
            <h4>RFID Digit Settings</h4>
            <?php if (!empty($success_message)) : ?>
                <div class="alert alert-success" id="successAlert">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            <div class="rfid-digit-wrapper">
                <h5>Register RFID UID for a User</h5>
                <form action="register_rfid.php" method="POST">
                    <div class="mb-3">
                        <label for="user_type" class="form-label">Select User Type</label>
                        <select name="user_type" id="user_type" class="form-control" required>
                            <option value="student">Student</option>
                            <option value="teacher">Teacher</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="lrn" class="form-label">LRN/Employee ID</label>
                        <input type="text" name="lrn" id="lrn" class="form-control" placeholder="Enter student's LRN or teacher's Employee ID" required>
                    </div>
                    <div class="mb-3">
                        <label for="rfid_uid" class="form-label">RFID UID</label>
                        <input type="text" name="rfid_uid" id="rfid_uid" class="form-control" placeholder="Enter RFID UID from scanner" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Register</button>
                </form>
            </div>
        </div>





                <div class="settings-panel <?php echo ($active_panel == 'classManagement') ? 'active' : ''; ?>" id="classManagement">
                    <h4>Class Management</h4>

                    <!-- Place the success alert here -->
                    <?php if (!empty($success_message)) : ?>
                        <div class="alert alert-success" id="successAlert">
                            <?php echo $success_message; ?>
                        </div>
                    <?php endif; ?>

                    <div class="class-management-wrapper">
                        <!-- Manage Sections -->
                        <div class="class-management-section">
                            <h5>Manage Sections</h5>
                            <form action="add_section.php" method="POST" class="mb-4">
                                <div class="input-group">
                                    <input type="text" name="section" placeholder="Section Name" class="form-control" required>
                                    <button type="submit" class="btn btn-primary">Add Section</button>
                                </div>
                            </form>
                            <!-- Display existing sections -->
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Section Name</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Fetch sections from the database
                                        $sections = mysqli_query($conn, "SELECT * FROM sections");
                                        while ($row = mysqli_fetch_assoc($sections)) {
                                            echo "<tr>";
                                            echo "<td>" . $row['section'] . "</td>";
                                            echo "<td>
                                                <button class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editModal" . $row['id'] . "'>Edit</button>
                                                <button class='btn btn-danger btn-sm' data-bs-toggle='modal' data-bs-target='#deleteModal" . $row['id'] . "'>Delete</button>
                                            </td>";
                                            echo "</tr>";

                                            // Edit Modal
                                            echo "<div class='modal fade' id='editModal" . $row['id'] . "' tabindex='-1' aria-labelledby='editModalLabel" . $row['id'] . "' aria-hidden='true'>
                                                    <div class='modal-dialog'>
                                                        <div class='modal-content'>
                                                            <div class='modal-header'>
                                                                <h5 class='modal-title' id='editModalLabel" . $row['id'] . "'>Edit Section</h5>
                                                                <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                                            </div>
                                                            <form action='edit_section.php' method='POST'>
                                                                <div class='modal-body'>
                                                                    <input type='hidden' name='id' value='" . $row['id'] . "'>
                                                                    <div class='mb-3'>
                                                                        <label for='section_name' class='form-label'>Section Name</label>
                                                                        <input type='text' class='form-control' id='section_name' name='section_name' value='" . $row['section'] . "' required>
                                                                    </div>
                                                                </div>
                                                                <div class='modal-footer'>
                                                                    <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                                                                    <button type='submit' class='btn btn-primary'>Save changes</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                  </div>";

                                            // Delete Modal
                                            echo "<div class='modal fade' id='deleteModal" . $row['id'] . "' tabindex='-1' aria-labelledby='deleteModalLabel" . $row['id'] . "' aria-hidden='true'>
                                                    <div class='modal-dialog'>
                                                        <div class='modal-content'>
                                                            <div class='modal-header'>
                                                                <h5 class='modal-title' id='deleteModalLabel" . $row['id'] . "'>Delete Section</h5>
                                                                <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                                            </div>
                                                            <form action='delete_section.php' method='POST'>
                                                                <div class='modal-body'>
                                                                    <input type='hidden' name='id' value='" . $row['id'] . "'>
                                                                    <p>Are you sure you want to delete this section?</p>
                                                                </div>
                                                                <div class='modal-footer'>
                                                                    <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                                                                    <button type='submit' class='btn btn-danger'>Delete</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                  </div>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Manage Grade Levels -->
                        <div class="class-management-grade">
                            <h5>Manage Grade Levels</h5>
                            <form action="add_grade_level.php" method="POST" class="mb-4">
                                <div class="input-group">
                                    <input type="text" name="grade_level_name" placeholder="Grade Level Name" class="form-control" required>
                                    <button type="submit" class="btn btn-primary">Add Grade Level</button>
                                </div>
                            </form>
                            <!-- Display existing grade levels -->
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Grade Level Name</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Fetch grade levels from the database
                                        $grade_levels = mysqli_query($conn, "SELECT * FROM grade_levels");
                                        while ($row = mysqli_fetch_assoc($grade_levels)) {
                                            echo "<tr>";
                                            echo "<td>" . $row['grade_level'] . "</td>";
                                            echo "<td>
                                                <button class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editGradeModal" . $row['id'] . "'>Edit</button>
                                                <button class='btn btn-danger btn-sm' data-bs-toggle='modal' data-bs-target='#deleteGradeModal" . $row['id'] . "'>Delete</button>
                                            </td>";
                                            echo "</tr>";

                                            // Edit Modal
                                            echo "<div class='modal fade' id='editGradeModal" . $row['id'] . "' tabindex='-1' aria-labelledby='editGradeModalLabel" . $row['id'] . "' aria-hidden='true'>
                                                    <div class='modal-dialog'>
                                                        <div class='modal-content'>
                                                            <div class='modal-header'>
                                                                <h5 class='modal-title' id='editGradeModalLabel" . $row['id'] . "'>Edit Grade Level</h5>
                                                                <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                                            </div>
                                                            <form action='edit_grade_level.php' method='POST'>
                                                                <div class='modal-body'>
                                                                    <input type='hidden' name='id' value='" . $row['id'] . "'>
                                                                    <div class='mb-3'>
                                                                        <label for='grade_level_name' class='form-label'>Grade Level Name</label>
                                                                        <input type='text' class='form-control' id='grade_level_name' name='grade_level_name' value='" . $row['grade_level'] . "' required>
                                                                    </div>
                                                                </div>
                                                                <div class='modal-footer'>
                                                                    <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                                                                    <button type='submit' class='btn btn-primary'>Save changes</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                  </div>";

                                            // Delete Modal
                                            echo "<div class='modal fade' id='deleteGradeModal" . $row['id'] . "' tabindex='-1' aria-labelledby='deleteGradeModalLabel" . $row['id'] . "' aria-hidden='true'>
                                                    <div class='modal-dialog'>
                                                        <div class='modal-content'>
                                                            <div class='modal-header'>
                                                                <h5 class='modal-title' id='deleteGradeModalLabel" . $row['id'] . "'>Delete Grade Level</h5>
                                                                <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                                            </div>
                                                            <form action='delete_grade_level.php' method='POST'>
                                                                <div class='modal-body'>
                                                                    <input type='hidden' name='id' value='" . $row['id'] . "'>
                                                                    <p>Are you sure you want to delete this grade level?</p>
                                                                </div>
                                                                <div class='modal-footer'>
                                                                    <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                                                                    <button type='submit' class='btn btn-danger'>Delete</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                  </div>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="settings-panel <?php echo ($active_panel == 'backupRestore') ? 'active' : ''; ?>" id="backupRestore">
                    <h4>Backup and Restore</h4>
                    <div class="backup-restore-wrapper">
                        <h5>Backup Database</h5>
                        <form action="backup_database.php" method="POST">
                            <button type="submit" class="btn btn-primary">Backup Now</button>
                        </form>

                        <h5 class="mt-4">Restore Database</h5>
                        <form action="restore_database.php" method="POST" enctype="multipart/form-data">
                            <div class="input-group mb-3">
                                <input type="file" name="backup_file" class="form-control" required>
                                <button type="submit" class="btn btn-danger">Restore</button>
                            </div>
                            <small class="form-text text-muted">Upload a SQL backup file to restore the database.</small>
                        </form>
                    </div>
                </div>


                <!--sms bulk-->
                <div class="settings-panel <?php echo ($active_panel == 'smsBlaster') ? 'active' : ''; ?>" id="smsBlaster">
                <h4>SMS Blaster</h4>
                <?php if (!empty($success_message)) : ?>
                    <div class="alert <?php echo $alert_class; ?>" id="successAlert">
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>

                <div class="sms-blaster-wrapper">
                    <h5>Send Bulk SMS</h5>
                    <form action="send_bulk_sms.php" method="POST">
                        <div class="mb-3">
                            <label for="sms_message" class="form-label">Message</label>
                            <textarea name="sms_message" id="sms_message" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="recipient_group" class="form-label">Recipient Group</label>
                            <select name="recipient_group" id="recipient_group" class="form-select" required>
                                <option value="all_students">All Students</option>
                                <option value="all_teachers">All Teachers</option>
                                <option value="all_parents">All Parents</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success">Send SMS</button>
                    </form>
                </div>
            </div>


            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="script3.js"></script>
    <script>
    $(document).ready(function(){
        // Auto-hide success alert after 5 seconds
        setTimeout(function() {
            $("#successAlert").fadeOut("slow");
        }, 1000);

        $('.content-sidebar ul li a').click(function(e){
            e.preventDefault();
            $('.content-sidebar ul li a').removeClass('active');
            $(this).addClass('active');

            var panelToShow = $(this).data('panel');
            $('.settings-panel').removeClass('active');
            $('#' + panelToShow).addClass('active');
        });

        $('#menubar').on('click', function() {
            $('#sidebar').toggleClass('collapsed');
            $('#mainContent').toggleClass('expanded');
        });
    });
</script>

</body>
</html>
