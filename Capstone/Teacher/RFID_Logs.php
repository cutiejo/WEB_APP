<?php
session_start(); // Start the session

// Check if the user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: ../Login/login.php"); // Redirect to login if not an admin
    exit();
}
include '../db.php';

// Fetch log data for the RFID logs section
$logs = $conn->query("SELECT logs.*, students.name FROM logs JOIN students ON logs.student_id = students.id ORDER BY timestamp DESC");
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

        .dataTables_length,
        .dataTables_length select {
            display: inline-block;
            vertical-align: middle;
        }

        .dataTables_wrapper .dataTables_length select {
            width: auto;
        }

        .btn-group .btn {
            margin-right: 5px;
        }

        .btn-group .btn i {
            margin-right: 5px;
        }
      
        .filter-btn {
            display: inline-block;
            margin-top: -5px;
            float: right;
        }

        .select-checkbox {
            margin-right: 10px;
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

        .btn-close {
            background: none;
            border: none;
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
                <!-- Add Generate Report Button -->
                <button class="btn btn-primary" id="generateReportBtn">
                    <i class="fas fa-file-pdf"></i> Generate Report
                </button>
            </div>
           
            <!-- Logs Table -->
            <div class="logs-table-container">
                <h7>Logs Table</h7>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="logsTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Grade Level</th>
                                <th>Section</th>
                                <th>RFID No</th>
                                <th>Status</th>
                                <th>Date</th>

                         <!--  <th>Time In</th>
                                <th>Time Out</th>
                               
                                  -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($log = $logs->fetch_assoc()): ?>
                                <tr>
                                    <td><input type="checkbox" class="select-checkbox"><?php echo $log['name']; ?></td>
                                    <td><?php echo $log['section']; ?></td>
                                    <td><?php echo $log['rfid_no']; ?></td>

                        <!--        <td><?php echo $log['time_in']; ?></td>
                                    <td><?php echo $log['time_out']; ?></td>
                                    <td><?php echo date('m/d/Y', strtotime($log['timestamp'])); ?></td>
                                
                                    <td>
                                        <?php if ($log['status'] === 'Late'): ?>
                                            <span class="text-danger">Late</span> 
                                        <?php else: ?>
                                            <span class="text-primary">Early</span>
                                        <?php endif; ?>
                                       -->
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
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
                            <label class="form-check-label" for="present">
                                Present
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="absent">
                            <label class="form-check-label" for="absent">
                                Absent
                            </label>
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
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Modal and DataTables Initialization Script -->

    <!-- Custom Script for DataTable Initialization and Tab Navigation -->
    <script>
        $(document).ready(function() {
            var table = $('#logsTable').DataTable({
                "paging": true,
                "ordering": true,
                "info": true
            });

            // Handle Generate Report button click
            $('#generateReportBtn').on('click', function() {
                // Navigate to the Logs tab in the Report module
                window.location.href = '/Capstone/teacher/Report.php#logs';
            });

            // Switch to the appropriate tab based on the URL hash
            var hash = window.location.hash;
            if (hash === '#logs') {
                $('#logs-tab').tab('show');
            }

            // Move buttons next to the show entries dropdown
            $('.dataTables_length').after(
                '<div class="btn-group">' +
                    '<button class="btn btn-outline-primary btn-sm" id="filterButton"><i class="fas fa-filter"></i> Filter</button>' +
                '</div>'
            );

            // Trigger the modal when the Filter button is clicked
            $('#filterButton').on('click', function() {
                $('#filterModal').modal('show');
            });

            // Ensure that "Present" and "Absent" checkboxes are mutually exclusive
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

            // Select/Deselect all checkboxes
            $('#select-all').on('click', function() {
                var rows = table.rows({ 'search': 'applied' }).nodes();
                $('input[type="checkbox"]', rows).prop('checked', this.checked);
            });

            $('#logsTable tbody').on('change', 'input[type="checkbox"]', function() {
                if (!this.checked) {
                    var el = $('#select-all').get(0);
                    if (el && el.checked && ('indeterminate' in el)) {
                        el.indeterminate = true;
                    }
                }
            });
        });
    </script>
</body>
</html>
