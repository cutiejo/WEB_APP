<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../Login/login.php"); // Redirect to login if not an admin
    exit();
}
include '../db.php';
require_once('../TCPDF-main/tcpdf.php');

// Fetch total number of students
$result = $conn->query("SELECT COUNT(id) as total_students FROM students");
if ($result && $row = $result->fetch_assoc()) {
    $total_students = $row['total_students'];
}

// Export Data Logic
if (isset($_POST['export']) && $_POST['export'] === 'pdf') {
    if (ob_get_length()) ob_end_clean();

    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

    // Set document information and header
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('');
    $pdf->SetTitle('Student Report');
    $pdf->SetSubject('Student Data');

    $logoLeft = '../assets/imgs/logo.jpg';
    $logoRight = '../assets/imgs/bagongPilipinas.jpg';
    $pdf->SetHeaderData($logoLeft, 30, '', '', array(0,0,0), array(255, 255, 255));

    // Set margins
    $pdf->SetMargins(5, 20, 5);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $pdf->AddPage();

    // Insert images and header text
    $pdf->Image($logoLeft, 8, 22, 30, '', '', '', '', false, 300, '', false, false, 0, false, false, false);
    $pdf->Image($logoRight, 170, 10, 30, '', '', '', '', false, 300, '', false, false, 0, false, false, false);
    
    $pdf->Ln(-10);
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 5, 'S.V Montessori Imus', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 5, 'Ph. 8 Brgy. Magdalo, Bahayang Pagasa Subdivision', 0, 1, 'C');
    $pdf->Cell(0, 5, 'Imus Cavite', 0, 1, 'C');
    $pdf->Cell(0, 5, '(046) 471-66-07 / (046) 471-67-70 / (046) 686-2349', 0, 1, 'C');
    $pdf->Cell(0, 5, 'www.S.V Montessori', 0, 1, 'C');

    $pdf->Ln(15);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Student Report', 0, 1, 'C');
    $pdf->Ln(5);

    // Build the table content with the specified fields
    $html = '<table border="1" cellpadding="3" cellspacing="0" style="width: 100%; text-align: center;">';
    $html .= '<thead>
                <tr style="font-size: 10px; font-weight: bold; text-align: center;">
                    <th style="width: 11.1%;">Name</th>
                    <th style="width: 11.1%;">Section</th>
                    <th style="width: 11.1%;">LRN</th>
                    <th style="width: 11.1%;">RFID Number</th>
                    <th style="width: 11.1%;">Total Class Days</th>
                    <th style="width: 11.1%;">Present Days</th>
                    <th style="width: 11.1%;">Absent Days</th>
                    <th style="width: 11.1%;">Late Days</th>
                    <th style="width: 11.1%;">Remark</th>
                </tr>
              </thead><tbody>';

    // Fetch the students' report data from the database
// Fetch the students' report data from the database
$reportQuery = "
    SELECT 
        s.full_name AS name, 
        s.section_id AS section, 
        s.lrn AS lrn, 
        s.rfid_uid AS RFID_Number, 
        r.total_class_days, 
        r.present_days, 
        r.absent_days, 
        r.late_days, 
        r.remark
    FROM 
        students s
    LEFT JOIN 
        StudentReport r ON s.id = r.student_id"; // Update based on your table structure

$reportResult = mysqli_query($conn, $reportQuery);

while ($row = mysqli_fetch_assoc($reportResult)) {
    $html .= '<tr style="font-size: 10px; text-align: center;">
                <td>' . ($row['name'] ?? 'N/A') . '</td>
                <td>' . ($row['section'] ?? 'N/A') . '</td>
                <td>' . ($row['lrn'] ?? 'N/A') . '</td>
                <td>' . ($row['RFID_Number'] ?? 'N/A') . '</td>
                <td>' . ($row['total_class_days'] ?? 'N/A') . '</td>
                <td>' . ($row['present_days'] ?? 'N/A') . '</td>
                <td>' . ($row['absent_days'] ?? 'N/A') . '</td>
                <td>' . ($row['late_days'] ?? 'N/A') . '</td>
                <td>' . ($row['remark'] ?? 'N/A') . '</td>
              </tr>';
}

    $html .= '</tbody></table>';

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('student_report.pdf', 'I');
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Common styles */
        .main-content {
            overflow-y: auto;
            height: 100%;
        }

        /* Filter Section */
.filter-section {
    display: flex;
    justify-content: flex-start; /* Align items to the left */
    align-items: center;
    flex-wrap: wrap;
    gap: 5px; /* Reduced spacing between elements */
    margin-bottom: 10px; /* Reduced margin at the bottom */
}

.filter-section .form-group {
    display: flex;
    align-items: center;
    margin-right: 5px; /* Reduced right margin */
    margin-bottom: 5px; /* Reduced bottom margin */
}

.filter-section .form-group label {
    margin-right: 3px; /* Reduced space between label and input */
}

.filter-section .form-group input,
.filter-section .form-group select {
    margin-right: 3px; /* Reduced space between form controls */
    width: 100px; /* Set a smaller width */
    padding: 5px; /* Reduced padding */
    font-size: 14px; /* Adjusted font size to fit content */
}

.filter-section button {
    background-color: #007bff;
    color: white;
    border: none;
    padding: 5px 10px; /* Reduced padding for the button */
    border-radius: 5px;
    font-size: 14px; /* Adjusted font size */
}

.filter-section button i {
    margin-right: 2px; /* Reduced margin for icon */
}


        .summary-container {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .chart-container {
            display: block;
            box-sizing: border-box;
            height: 100px;
            width: 188px;
        }

        .cards-container {
            display: flex;
            justify-content: flex-start;
            flex-wrap: wrap;
            width: 80%;
        }

        .summary-card {
            background: white;
            border-radius: 10px;
            padding: 7px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: left;
            margin: 10px;
            flex: 1;
            border-left: 5px solid transparent;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .summary-card p {
            margin: 0;
        }

        .summary-card h3 {
            margin: 10px 0;
        }

        .summary-card.total-student {
            border-left-color: #007bff;
        }

        .summary-card.present {
            border-left-color: #007bff;
        }

        .summary-card.absent {
            border-left-color: #dc3545;
        }

        .summary-card.late {
            border-left-color: #ffc107;
        }

        .summary-card:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .report-table-container {
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .logs-summary-container {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .logs-chart-container {
            width: 20%;
        }

        .logs-cards-container {
            display: flex;
            justify-content: flex-start;
            flex-wrap: wrap;
            width: 80%;
        }

        .logs-summary-card {
            background: white;
            border-radius: 10px;
            padding: 7px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: left;
            margin: 10px;
            flex: 1;
            border-left: 5px solid transparent;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .logs-summary-card p {
            margin: 0;
        }

        .logs-summary-card h3 {
            margin: 10px 0;
        }

        .logs-summary-card.total-student {
            border-left-color: #007bff;
        }

        .logs-summary-card.present {
            border-left-color: #007bff;
        }

        .logs-summary-card.absent {
            border-left-color: #dc3545;
        }

        .logs-summary-card.late {
            border-left-color: #ffc107;
        }

        .logs-summary-card:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .logs-table-container {
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }

        .text-end.mt-3 {
            display: flex;
            justify-content: flex-end;
            margin-top: 10px;
        }
    </style>
</head>

<body>
<?php include 'navbar.php'; ?>
    <div class="scrollable-content" style="overflow-y: auto; max-height: calc(100vh - 100px);">
    <div class="main-content" id="main-content">
        <div class="container-fluid">
            <!-- Tabs for Report and Logs -->
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="report-tab" data-bs-toggle="tab" data-bs-target="#report" type="button" role="tab" aria-controls="report" aria-selected="true">Report</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logs" type="button" role="tab" aria-controls="logs" aria-selected="false">Logs</button>
                </li>
            </ul>

            <!-- Tab Contents -->
            <div class="tab-content" id="myTabContent">
                <!-- Report Tab -->
                <div class="tab-pane fade show active" id="report" role="tabpanel" aria-labelledby="report-tab">
                    <div class="filter-section mt-3">
                        <div class="form-group">
                            <label for="name">Name:</label>
                            <input type="text" id="name" class="form-control" value="Baltazar">
                        </div>
                        <div class="form-group">
                            <label for="section">Section:</label>
                            <select id="section" class="form-control">
                                <option>Baltazar</option>
                                <option>33</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="from-date">From:</label>
                            <input type="date" id="from-date" class="form-control" value="2024-07-27">
                        </div>
                        <div class="form-group">
                            <label for="to-date">To:</label>
                            <input type="date" id="to-date" class="form-control" value="2024-07-27">
                        </div>
                        <button type="button" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>

                    <div class="summary-container">
                        <div class="chart-container">
                            <canvas id="myChart"></canvas>
                        </div>
                        <div class="cards-container">
                            <div class="summary-card total-student">
                                <p>Total Student</p>
                                <h3 class="text-primary">1000</h3>
                            </div>
                            <div class="summary-card present">
                                <p>Present</p>
                                <h3 class="text-primary">600</h3>
                            </div>
                            <div class="summary-card absent">
                                <p>Absent</p>
                                <h3 class="text-danger">400</h3>
                            </div>
                            <div class="summary-card late">
                                <p>Late</p>
                                <h3 class="text-warning">200</h3>
                            </div>
                        </div>
                    </div>

                    <div class="report-table-container">
                        <table id="reportTable" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Section</th>
                                    <th>LRN</th>
                                    <th>RFID No</th>
                                    <th>Total of Present</th>
                                    <th>Total of Absent</th>
                                    <th>Total of Late</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be populated here via AJAX -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Button to Trigger the Modal -->
                    <div class="text-end mt-3">
                        <button class="btn btn-success" id="exportButton">
                            <i class="fas fa-file-excel"></i> Export Data
                        </button>
                    </div>
                </div>

                <!-- Logs Tab -->
                <div class="tab-pane fade" id="logs" role="tabpanel" aria-labelledby="logs-tab">
                    
                    <div class="filter-section mt-3">
                        <div class="form-group">
                            <label for="log-name">Name:</label>
                            <input type="text" id="log-name" class="form-control" value="Baltazar">
                        </div>
                        <div class="form-group">
                            <label for="log-section">Section:</label>
                            <select id="log-section" class="form-control">
                                <option>Baltazar</option>
                                <option>33</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="log-from-date">From:</label>
                            <input type="date" id="log-from-date" class="form-control" value="2024-07-27">
                        </div>
                        <div class="form-group">
                            <label for="log-to-date">To:</label>
                            <input type="date" id="log-to-date" class="form-control" value="2024-07-27">
                        </div>
                        <button type="button" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>

                    <div class="logs-summary-container">
                        <div class="logs-chart-container">
                            <canvas id="logChart"></canvas>
                        </div>
                        <div class="logs-cards-container">
                            <div class="logs-summary-card total-student">
                                <p>Total Student</p>
                                <h3 class="text-primary">1000</h3>
                            </div>
                            <div class="logs-summary-card present">
                                <p>Present</p>
                                <h3 class="text-primary">600</h3>
                            </div>
                            <div class="logs-summary-card absent">
                                <p>Absent</p>
                                <h3 class="text-danger">400</h3>
                            </div>
                            <div class="logs-summary-card late">
                                <p>Late</p>
                                <h3 class="text-warning">200</h3>
                            </div>
                        </div>
                    </div>

                    <div class="logs-table-container">
                        <table id="logsTable" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Section</th>
                                    <th>Grade Level</th>
                                    <th>RFID No</th>
                                    <th>Status</th> 
                                    <th>Date</th>
                                   <!--  <th>Time In</th>
                                    <th>Time Out</th> -->
                                   
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be populated here via AJAX -->
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-3">
                        <button class="btn btn-primary" id="printLogsButton">
                            <i class="fas fa-print"></i> Print Logs
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Structure -->
    <div id="exportModal" class="modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Generate Report</h5>
                    <button type="button" class="close">&times;</button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label for="Name" class="form-label">Name:</label>
                            <input type="text" class="form-control" id="Name">
                        </div>
                        <div class="mb-3">
                            <label for="Section" class="form-label">Section:</label>
                            <input type="text" class="form-control" id="Section">
                        </div>
                        <div class="mb-3">
                            <label for="LRN" class="form-label">LRN:</label>
                            <input type="text" class="form-control" id="LRN">
                        </div>
                        <div class="mb-3">
                            <label for="rfid-number" class="form-label">RFID Number:</label>
                            <input type="text" class="form-control" id="rfid-number">
                        </div>
                        <div class="mb-3">
                            <label for="total-class-days" class="form-label">Total Class Days:</label>
                            <input type="text" class="form-control" id="total-class-days">
                        </div>
                        <div class="mb-3">
                            <label for="present-days" class="form-label">Present Days:</label>
                            <input type="text" class="form-control" id="present-days">
                        </div>
                        <div class="mb-3">
                            <label for="absent-days" class="form-label">Absent Days:</label>
                            <input type="text" class="form-control" id="absent-days">
                        </div>
                        <div class="mb-3">
                            <label for="late-days" class="form-label">Late Days:</label>
                            <input type="text" class="form-control" id="late-days">
                        </div>
                        <div class="mb-3">
                            <label for="remark" class="form-label">Remark:</label>
                            <textarea class="form-control" id="remark">frequent tardies; parental meetings recommended.</textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                <form method="post">
                    <button type="submit" name="export" value="pdf" class="btn btn-danger" data-bs-toggle="tooltip" id="generateReportBtn">
                        <i class="fas fa-file-pdf"></i> Generate Report
                    </button>
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
    <script>
        $(document).ready(function() {
            // Initialize DataTables
            $('table').each(function() {
                $(this).DataTable({
                    "order": [], // Disable initial ordering
                    "paging": true,
                    "searching": true,
                    "info": true
                });
            });

            // Handle Modal Logic
            var modal = document.getElementById("exportModal");
            var btn = document.getElementById("exportButton");
            var closeBtn = document.querySelector('.close');

            btn.onclick = function() {
                modal.style.display = "block";
            }

            closeBtn.onclick = function() {
                modal.style.display = "none";
            }

            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }

            // Handle Generate Report button click
            $('#generateReportBtn').on('click', function() {
                // Navigate to the Logs tab in the Report module
                window.location.href = '/capstone-main/teacher/Report.php#logs';
            });

            // Switch to the appropriate tab based on the URL hash
            var hash = window.location.hash;
            if (hash === '#logs') {
                $('#logs-tab').tab('show');
            }
        });

        document.getElementById('printLogsButton').addEventListener('click', function() {
            var printContents = document.getElementById('logsTable').outerHTML;
            var originalContents = document.body.innerHTML;

            document.body.innerHTML = printContents;

            window.print();

            document.body.innerHTML = originalContents;
            location.reload(); // Reload the page to restore the original contents after printing
        });

        // Chart.js Initialization
        var ctx = document.getElementById('myChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Present', 'Absent', 'Late'],
                datasets: [{
                    data: [600, 400, 200],
                    backgroundColor: ['#007bff', '#dc3545', '#ffc107']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed !== null) {
                                    label += context.parsed;
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });

        // Separate initialization for Logs Tab chart (if necessary)
        var logCtx = document.getElementById('logChart').getContext('2d');
        var logChart = new Chart(logCtx, {
            type: 'pie',
            data: {
                labels: ['Present', 'Absent', 'Late'],
                datasets: [{
                    data: [600, 400, 200],
                    backgroundColor: ['#007bff', '#dc3545', '#ffc107']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed !== null) {
                                    label += context.parsed;
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>

</html>
