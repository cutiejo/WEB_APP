<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../Login/login.php");
    exit();
}

include '../db.php';
require_once('../TCPDF-main/tcpdf.php');

// Initialize filter variables
$filters = [];
$whereClause = "";

// Handle Filter Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filter'])) {
    // Add conditions to the $filters array based on selected values
    if (!empty($_POST['role']) && $_POST['role'] != 'All') {
        $filters[] = "a.role = '" . mysqli_real_escape_string($conn, $_POST['role']) . "'";
    }
    if (!empty($_POST['grade_level'])) {
        $filters[] = "g.grade_level = '" . mysqli_real_escape_string($conn, $_POST['grade_level']) . "'";
    }
    if (!empty($_POST['section'])) {
        $filters[] = "s.section = '" . mysqli_real_escape_string($conn, $_POST['section']) . "'";
    }
    if (!empty($_POST['from'])) {
        $filters[] = "a.scan_time >= '" . mysqli_real_escape_string($conn, $_POST['from']) . "'";
    }
    if (!empty($_POST['to'])) {
        $filters[] = "a.scan_time <= '" . mysqli_real_escape_string($conn, $_POST['to']) . " 23:59:59'";
    }

    // Construct the WHERE clause
    if (!empty($filters)) {
        $whereClause = "WHERE " . implode(" AND ", $filters);
    }
}

/// Check for last scan status to toggle between "IN" and "OUT"
function getLastEventStatus($rfid_uid, $conn) {
    $query = "SELECT event_type FROM attendance WHERE rfid_uid = '$rfid_uid' ORDER BY scan_time DESC LIMIT 1";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['event_type'];
    }
    return null; // If no previous event
}

// Set event type based on last status (preventing double IN/OUT)
function determineEventType($rfid_uid, $conn) {
    $lastEvent = getLastEventStatus($rfid_uid, $conn);
    return ($lastEvent === 'IN') ? 'OUT' : 'IN';
}

// Fetch attendance records with filters applied
$attendanceQuery = "SELECT a.*, 
                           IFNULL(g.grade_level, 'N/A') AS grade_level_name, 
                           IFNULL(s.section, 'N/A') AS section_name 
                    FROM attendance a 
                    LEFT JOIN grade_levels g ON a.grade_level = g.id 
                    LEFT JOIN sections s ON a.section = s.id 
                    $whereClause 
                    ORDER BY a.scan_time DESC";

$attendanceResult = mysqli_query($conn, $attendanceQuery);
// Export Data Logic
if (isset($_POST['export'])) {
    if ($_POST['export'] === 'pdf') {
        // Ensure no output before creating the PDF
        if (ob_get_length()) ob_end_clean();
        
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // Set document information and header
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('');
        $pdf->SetTitle('Attendance Report');
        $pdf->SetSubject('Attendance Data');

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
        $pdf->Cell(0, 5, 'Imus Cavite ', 0, 1, 'C');
        $pdf->Cell(0, 5, '(046) 471-66-07 / (046) 471-67-70 / (046) 686-2349', 0, 1, 'C');
        $pdf->Cell(0, 5, 'www.S.V Montessori', 0, 1, 'C');
        
        // Add space above the "Attendance Report" title
        $pdf->Ln(15);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Attendance Report', 0, 1, 'C');
        $pdf->Ln(5);

        // Build the table content with centered alignment
        $html = '<table border="1" cellpadding="3" cellspacing="0" style="width: 100%; text-align: center;">';
        $html .= '<thead>
                    <tr style="font-size: 10px; font-weight: bold; text-align: center;">
                        <th style="width: 14.3%;">RFID Tag</th>
                        <th style="width: 14.3%;">Full Name</th>
                        <th style="width: 14.3%;">Grade Level</th>
                        <th style="width: 14.3%;">Section</th>
                        <th style="width: 14.3%;">Scan Time</th>
                        <th style="width: 14.3%;">Event Type</th>
                        <th style="width: 14.3%;">Status</th>
                    </tr>
                </thead><tbody>';

        while ($row = mysqli_fetch_assoc($attendanceResult)) {
            $html .= '<tr style="font-size: 10px; text-align: center;">
                        <td>' . ($row['rfid_uid'] ?? 'N/A') . '</td>
                        <td>' . ($row['full_name'] ?? 'N/A') . '</td>
                        <td>' . ($row['grade_level_name'] ?? 'N/A') . '</td>
                        <td>' . ($row['section_name'] ?? 'N/A') . '</td>
                        <td>' . ($row['scan_time'] ?? 'N/A') . '</td>
                        <td>' . ($row['event_type'] ?? 'N/A') . '</td>
                        <td>' . ($row['status'] ?? 'N/A') . '</td>
                    </tr>';
        }
        $html .= '</tbody></table>';

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('attendance_report.pdf', 'I');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="styles.css">
</head>
<style>
        /* Styling adjustments */
        .main-content {
            display: flex;
            flex-wrap: wrap;
            height: calc(100vh - 56px);
            overflow-y: auto;
        }
        .content-section {
            overflow-y: auto;
            padding: 20px;
            height: calc(100vh - 110px);
            background-color: #f8f9fa;
        }
        .filter-form {
            display: flex;
            align-items: center;
            gap: 3px;
            margin-bottom: 20px;
            padding-bottom: 20px;
            position: relative;
            justify-content: space-between;
            flex-wrap: wrap;
            z-index: 1;
        }
        .filter-form input, .filter-form select {
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        .filter-form .btn {
            padding: 5px 10px;
            border-radius: 4px;
        }
        .table-responsive {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }
        #attendanceTable {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        #attendanceTable thead th {
            background-color: #f8f9fa;
            color: #333;
            font-weight: bold;
            text-align: center;
            padding: 12px;
            border-bottom: 2px solid #dee2e6;
        }
        #attendanceTable tbody td {
            text-align: center;
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }
        #attendanceTable tbody tr:hover {
            background-color: #f8f9fa;
            transition: background-color 0.3s ease-in-out;
        }
    </style>
<body>
<?php include 'navbar.php'; ?>
<div class="main-content" id="main-content">
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Attendance</h1>
            <form method="post">
                <button type="submit" name="export" value="csv" class="btn btn-success" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Export as CSV">Export as CSV</button>
                <button type="submit" name="export" value="pdf" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Export as PDF">Export as PDF</button>
            </form>
        </div>

        <!-- Scrollable Content Section -->
        <div class="content-section">
            <!-- Filter Form -->
            <form method="post" class="filter-form">
                <label for="role">Role:</label>
                <select name="role" id="role">
                    <option value="All">All</option>
                    <option value="Student">Student</option>
                    <option value="Teacher">Teacher</option>
                </select>

                <label for="grade_level">Grade Level:</label>
                <select name="grade_level" id="grade_level">
                    <option value="">Select Grade Level</option>
                    <!-- Fetch Grade Levels from Database -->
                    <?php
                    $gradeLevelsQuery = "SELECT * FROM grade_levels";
                    $gradeLevelsResult = mysqli_query($conn, $gradeLevelsQuery);
                    while ($grade = mysqli_fetch_assoc($gradeLevelsResult)) {
                        echo "<option value=\"" . $grade['grade_level'] . "\">" . $grade['grade_level'] . "</option>";
                    }
                    ?>
                </select>

                <label for="section">Section:</label>
                <select name="section" id="section">
                    <option value="">Select Section</option>
                    <!-- Fetch Sections from Database -->
                    <?php
                    $sectionsQuery = "SELECT * FROM sections";
                    $sectionsResult = mysqli_query($conn, $sectionsQuery);
                    while ($section = mysqli_fetch_assoc($sectionsResult)) {
                        echo "<option value=\"" . $section['section'] . "\">" . $section['section'] . "</option>";
                    }
                    ?>
                </select>

                <label for="from">From:</label>
                <input type="date" name="from" id="from">

                <label for="to">To:</label>
                <input type="date" name="to" id="to">

                <button type="submit" class="btn btn-primary">Filter</button>
            </form>

            <!-- Attendance Table -->
            <div class="table-responsive">
                <table id="attendanceTable" class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th>Attendance ID</th>
                        <th>User ID</th>
                        <th>RFID Tag</th>
                        <th>Full Name</th>
                        <th>Role</th>
                        <th>Grade Level</th>
                        <th>Section</th>
                        <th>Scan Time</th>
                        <th>Event Type</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    if ($attendanceResult) {
                        while ($row = mysqli_fetch_assoc($attendanceResult)) {
                            echo "<tr>";
                            echo "<td>" . $row['attendance_id'] . "</td>";
                            echo "<td>" . $row['user_id'] . "</td>";
                            echo "<td>" . $row['rfid_uid'] . "</td>";
                            echo "<td>" . $row['full_name'] . "</td>";
                            echo "<td>" . $row['role'] . "</td>";
                            echo "<td>" . $row['grade_level_name'] . "</td>"; // Display grade level number
                            echo "<td>" . $row['section_name'] . "</td>"; // Display section name
                            echo "<td>" . $row['scan_time'] . "</td>";
                            echo "<td>" . $row['event_type'] . "</td>";
                            echo "<td>" . $row['status'] . "</td>";
                            echo "</tr>";
                        }
                    }

                    ?>
                    </tbody>

                </table>
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
<script>
    $(document).ready(function () {
        // Initialize DataTable
        $('#attendanceTable').DataTable();
    });

    
</script>
</body>
</html>
