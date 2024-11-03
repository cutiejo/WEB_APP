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
        height: auto; /* Let the content define the height */
        max-height: none; /* Removes the artificial height restriction */
        overflow: visible; /* Removes the inner scroll bar */
    }

    .table-responsive {
        overflow: visible; /* Removes the inner scroll bar */
    }

    /* Individual notification item */
    .notification-item {
        display: flex; /* Flexbox to align content and image */
        align-items: center;
        justify-content: space-between; /* Space between text and image */
        background-color: #f9f9f9; /* Light background for each notification */
        border: 1px solid #ddd;
        padding: 15px;
        margin-bottom: 15px;
        border-radius: 8px;
        transition: background-color 0.3s ease;
    }

    /* Add hover effect for notifications */
    .notification-item:hover {
        background-color: #e9ecef;
    }

    .notification-item h6 {
        margin: 0;
        font-weight: bold;
        font-size: 16px;
    }

    .notification-item p {
        margin: 0;
        font-size: 12px;
        color: #666;
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
                <h1 class="h3 mb-0 text-gray-800">Notification</h1>
                
            </div>
           
           <!-- Logs Table -->
<div class="logs-table-container">
    <div class="table-responsive">


<!-- Logs Table -->
<div class="logs-table-container">
    <div class="table-responsive">
        <!-- Notification items -->
        <div class="notification-item">
            <h6>John Doe marked as present</h6>
            <p>10/9/2024</p>
        </div>
        <div class="notification-item">
            <h6>New announcement posted</h6>
            <p>10/7/2024</p>
        </div>
        <!-- Add more notification items as needed -->
        <div class="notification-item">
            <h6>New announcement posted</h6>
            <p>10/7/2024</p>
        </div>
        <div class="notification-item">
            <h6>New announcement posted</h6>
            <p>10/7/2024</p>
        </div>

        <div class="notification-item">
            <h6>New announcement posted</h6>
            <p>10/7/2024</p>
        </div>

        <div class="notification-item">
            <h6>New announcement posted</h6>
            <p>10/7/2024</p>
        </div>

        <div class="notification-item">
            <h6>New announcement posted</h6>
            <p>10/7/2024</p>
        </div>

        <div class="notification-item">
            <h6>New announcement posted</h6>
            <p>10/7/2024</p>
        </div>

        <div class="notification-item">
            <h6>New announcement posted</h6>
            <p>10/7/2024</p>
        </div>

        <div class="notification-item">
            <h6>New announcement posted</h6>
            <p>10/7/2024</p>
        </div>

        <div class="notification-item">
            <h6>New announcement posted</h6>
            <p>10/7/2024</p>
        </div>

        <div class="notification-item">
            <h6>New announcement posted</h6>
            <p>10/7/2024</p>
        </div>

        <div class="notification-item">
            <h6>New announcement posted</h6>
            <p>10/7/2024</p>
        </div>

        <div class="notification-item">
            <h6>New announcement posted</h6>
            <p>10/7/2024</p>
        </div>
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

   
</body>
</html>