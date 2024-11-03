<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../Login/login.php");
    exit();
}

include '../db.php'; // Include database connection

// Fetch RFID logs with grade level and section joins
$rfidLogsQuery = "
    SELECT a.*, 
           g.grade_level AS grade_level_name, 
           s.section AS section_name 
    FROM attendance a 
    LEFT JOIN grade_levels g ON a.grade_level = g.id 
    LEFT JOIN sections s ON a.section = s.id 
    ORDER BY scan_time DESC";
$rfidLogsResult = mysqli_query($conn, $rfidLogsQuery);

if (!$rfidLogsResult) {
    echo "Error: " . mysqli_error($conn);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Logs</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="styles.css">

    <style>
        /* Container for the logs table */
        .logs-table-container {
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        /* Styling for DataTables elements */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_paginate,
        .dataTables_wrapper .dataTables_info {
            margin-top: 20px;
        }

        .btn-group {
            margin-left: 20px;
            display: inline-block;
        }
      
        /* Modal specific CSS */
        .modal-content {
            border-radius: 10px;
            padding: 20px;
        }

        .modal-header {
            border-bottom: none;
        }

        .modal-footer {
            border-top: none;
        }

        .modal-title {
            font-weight: bold;
        }
        
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
   
    <!-- Main Content -->
    <div class="main-content" id="main-content">
        <div class="container-fluid">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">RFID Logs</h1>
                <button class="btn btn-primary" id="generateReportBtn">
                    <i class="fas fa-file-pdf"></i> Generate Report
                </button>
            </div>

            <!-- Logs Table Container -->
            <div class="container logs-table-container">
                <table id="logsTable" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>RFID UID</th>
                            <th>User ID</th>
                            <th>Full Name</th>
                            <th>Grade Level</th>
                            <th>Section</th>
                            <th>Scan Time</th>
                            <th>Event Type</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($rfidLogsResult)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['rfid_uid']); ?></td>
                                <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['grade_level_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['section_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['scan_time']); ?></td>
                                <td><?php echo htmlspecialchars($row['event_type']); ?></td>
                                <td><?php echo htmlspecialchars($row['status']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Filter Logs Modal -->
    <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="filterModalLabel">Filter Logs</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label for="from-date" class="form-label">From:</label>
                            <input type="date" class="form-control" id="from-date">
                        </div>
                        <div class="mb-3">
                            <label for="to-date" class="form-label">To:</label>
                            <input type="date" class="form-control" id="to-date">
                        </div>
                        <div class="mb-3">
                            <label for="name" class="name-form">Name</label>
                            <input type="text" class="form-control" id="name">
                        </div>
                        <div class="mb-3">
                            <label for="section" class="form-label">Section:</label>
                            <select class="form-select" id="section">
                                <option selected>Select Section</option>
                                <option value="1">Section 1</option>
                                <option value="2">Section 2</option>
                                <option value="3">Section 3</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="yr-level" class="form-label">Yr Level:</label>
                            <select class="form-select" id="yr-level">
                                <option selected>Select Yr Level</option>
                                <option value="1">Level 1</option>
                                <option value="2">Level 2</option>
                                <option value="3">Level 3</option>
                            </select>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="present">
                            <label class="form-check-label" for="present">Present</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="absent">
                            <label class="form-check-label" for="absent">Absent</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary">Apply</button>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#logsTable').DataTable({
                paging: true,
                ordering: true,
                info: true
            });

            
            // Handle Generate Report button click
            $('#generateReportBtn').on('click', function() {
                // Navigate to the Logs tab in the Report module
                window.location.href = '../Admin/report.php#logs';
            });

            // Open the filter modal on button click
            $('#filterButton').on('click', function() {
                $('#filterModal').modal('show');
            });

            // Ensure "Present" and "Absent" checkboxes are mutually exclusive
            $('#present').on('change', function() {
                if (this.checked) {
                    $('#absent').prop('checked', false);
                }
            });
            $('#absent').on('change', function() {
                if (this.checked) {
                    $('#present').prop('checked', false);
                }
            });
        });
    </script>
</body>
</html>
