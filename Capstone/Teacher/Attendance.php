<?php
session_start(); // Start the session

// Check if the user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: ../Login/login.php"); // Redirect to login if not an admin
    exit();
}
include '../db.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendace</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="styles.css">
    <style>
    /* Main Content Layout */
    
    .main-content {
        display: flex;
        flex-wrap: wrap; /* Allows wrapping on smaller screens */
        height: calc(100vh - 56px); /*allows the content to go behind when you scroll down */
        overflow-y: auto; /*allows the content to go behind when you scroll down */
    }

    /* Sidebar */
    .sidebarw {
        width: 60px;
        background-color: white;
        padding: 20px 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        border-right: 1px solid #ddd;
        border-radius: .8rem;
        box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
        margin-right: 20px; /* Space between sidebar and content */
        position: relative;
        top: 185px; /* Moves the sidebar down */
        margin-top: -70px; /* Space between sidebar and content */
    }

    .grade-list {
        padding: 0;
        list-style: none;
        width: 100%;
        margin: auto;
        margin-left: 9px;
        position: relative;
       /* top: 45px; /* Moves the sidebar grade list up and down */
        margin-top: -20px; /* Space between sidebar and content */
    }

    .grade-list .grade-item {
        text-align: center;
        margin: 10px 0;
        background-color: #f8f9fa;
        border-radius: 40%;
        color: #218838;
        cursor: pointer;
        font-weight: bold;
        width: 40px;
        height: 40px;
        line-height: 40px;
        display: flex;
        justify-content: center;
        align-items: center;
        box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
        margin: 10px 0;
    }

    .grade-list .grade-item.active,
    .grade-list .grade-item:hover {
        background-color: #1cc88a;
        color: #fff;
    }

    /* Scrollable Content Section */
    .content-section {
        overflow-y: auto; /* Enables vertical scrolling for the entire content section */
        padding: 20px; /* Optional: Adds padding inside the scrollable content area */
        height: calc(100vh - 110px); /* Adjust the height of the scrollable content section */
        background-color: #f8f9fa; /* Light grey background for contrast */
    }

    /* Filter Form Container */
    .filter-form {
        display: flex;
        align-items: center;
        gap: 3px;
        margin-bottom: 20px;
        margin-top: -20px;
        padding-bottom: 20px;
        position: relative; /* Use relative positioning for flexibility */
        justify-content: space-between;
        flex-wrap: wrap;
        z-index: 1; /* Keep it above other elements */
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

    /* Grade Indicator Styling */
    .grade-indicator {
    margin: 10px auto; /* Increase or decrease the top and bottom margins for vertical positioning */
    font-size: 15px;
    font-weight: bold;
    color: #fff;
    text-align: center;
    padding: 8px;
    border-radius: 8px;
    background-color: #007bff;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    max-width: 100px;
  /*  position: relative;  To position it correctly inside the form 
        left: -0px;  Aligns it to the left *
        top: -15px;  Adjust based on spacing 
        display: inline-block;*/
        margin-left: -0px; /* Ensure space between the form and indicator */
        margin-top: -17px;
}


    /* Additional Styles for Page Heading */
    .page-heading {
        margin-bottom: 20px;
        margin-left: -72px;
    }

    /* Custom styles for the attendance table */
    .table-responsive {
        max-height: 400px; /* Adjust the height as necessary */
        overflow-y: auto;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 10px;
        box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
        background-color: #fff; /* White background for the table */
    }

    #attendanceTable {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 1rem;
        background-color: #fff; /* Ensures table itself has a white background */
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
    .table-responsive{
       max-width: 100%;
    }




    @media (max-width: 1200px) {
    .sidebarw {
        width: 50px;
    }

    .grade-list .grade-item {
        width: 35px;
        height: 35px;
        line-height: 35px;
    }
    

    @media (max-width: 992px) {
    .main-content {
        flex-direction: column;
    }

    .sidebarw {
        width: 100%;
        flex-direction: row;
        justify-content: space-around;
    }

    .content-section {
        height: auto;
        margin-top: 20px;
    }
}
@media (max-width: 768px) {
    .filter-form {
        flex-direction: column;
    }

    .filter-form input, .filter-form select, .filter-form .btn {
        width: 100%; /* Full width for inputs on smaller screens */
    }

    .grade-list .grade-item {
        width: 30px;
        height: 30px;
        line-height: 30px;
    }

    .grade-indicator {
        font-size: 12px;
        max-width: 80px;
        padding: 6px;
    }
}
@media (max-width: 576px) {
    .sidebarw {
        width: 100%;
    }

    .grade-list .grade-item {
        width: 25px;
        height: 25px;
        line-height: 25px;
    }

    .grade-indicator {
        font-size: 10px;
    }
}

}
</style>
</head>

<body>
<?php include 'navbar.php'; ?>

    <!-- Main Content -->
    <div class="main-content" id="main-content">
        <!-- Assigned Grade Level Sidebar -->
        <div class="sidebarw" id="sidebar">
            <ul class="grade-list">
                <li class="grade-item active" data-grade="Grade 1">G1</li>
                <li class="grade-item" data-grade="Grade 2">G2</li>
                <li class="grade-item" data-grade="Grade 3">G3</li>
                <li class="grade-item" data-grade="Grade 4">G4</li>
                <li class="grade-item" data-grade="Grade 5">G5</li>
                <li class="grade-item" data-grade="Grade 6">G6</li>
                <li class="grade-item" data-grade="Grade 7">G7</li>
                <li class="grade-item" data-grade="Grade 8">G8</li>
                <li class="grade-item" data-grade="Grade 9">G9</li>
                <li class="grade-item" data-grade="Grade 10">G10</li>
            </ul>
        </div>

        <!-- Content Area -->
        <div style="flex-grow: 1;">
            <!-- Page Heading -->
            <div class="page-heading d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Attendance</h1>
            </div>

            <!-- Scrollable Content Section -->
            <div class="content-section">
                <!-- Filter Form -->
                <div class="filter-form">
                    <label for="section">Section:</label>
                    <select id="section">
                        <option value="">Select</option>
                        <!-- Add more options here -->
                    </select>

                    <label for="from">From:</label>
                    <input type="date" id="from">

                    <label for="to">To:</label>
                    <input type="date" id="to">
                    

                    <label for="absent">Absent:</label>
                    <input type="checkbox" id="absent" name="attendance-status">

                    <label for="present">Present:</label>
                    <input type="checkbox" id="present" name="attendance-status">

                    <button type="button" class="btn btn-primary">Filter</button>
                    <button type="button" class="btn btn-success">Export Data</button>
                </div>

                <!-- Grade Level Indicator -->
                <div class="grade-indicator" id="gradeIndicator">Grade 1</div>

                <!-- Student Attendance Table -->
                <div class="table-responsive">
                    <table id="attendanceTable" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>LRN</th>
                                <th>RFID Tag</th>
                                <th>Name</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                                <th>Status</th>  
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Example rows, replace with dynamic content from the database -->
                            <tr>
                                <td>123456789</td>
                                <td>ABC123</td>
                                <td>John Doe</td>
                                <td>08:00 AM</td>
                                <td>04:00 PM</td>
                                <td>present</td>
                            </tr>
                            <!-- Add more rows as needed -->
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
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Modal and DataTables Initialization Script -->

    <script>
        $(document).ready(function () {
            // Initialize DataTable
            $('#attendanceTable').DataTable();

            // Handle Grade Item Click
            $('.grade-item').on('click', function () {
                // Remove 'active' class from all grade items
                $('.grade-item').removeClass('active');

                // Add 'active' class to the clicked item
                $(this).addClass('active');

                // Update the grade indicator based on the clicked item
                var gradeText = $(this).data('grade');
                $('#gradeIndicator').text(gradeText);
            });

            // Get the checkboxes
            const absentCheckbox = document.getElementById('absent');
            const presentCheckbox = document.getElementById('present');

            // Add event listeners to checkboxes
            absentCheckbox.addEventListener('change', function () {
                if (this.checked) {
                    presentCheckbox.checked = false;
                }
            });

            presentCheckbox.addEventListener('change', function () {
                if (this.checked) {
                    absentCheckbox.checked = false;
                }
            });
        });
    </script>
</body>
</html>
