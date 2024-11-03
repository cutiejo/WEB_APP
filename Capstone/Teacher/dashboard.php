<?php
session_start(); // Start the session

// Check if the user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: ../Login/login.php"); // Redirect to login if not an admin
    exit();
}
include '../db.php';


$teacher_id = $_SESSION['user_id']; // Get the logged-in teacher's ID
// Initialize variables
$total_students = $total_present = $total_absent = $total_late = 0;

// Fetch total number of students
$result = $conn->query("SELECT COUNT(id) as total_students FROM students");
if ($result && $row = $result->fetch_assoc()) {
    $total_students = $row['total_students'];
}

// Fetch total number of present students
$result = $conn->query("SELECT COUNT(id) as total_present FROM attendance WHERE status = 'Present'");
if ($result && $row = $result->fetch_assoc()) {
    $total_present = $row['total_present'];
}

// Fetch total number of absent students
$result = $conn->query("SELECT COUNT(id) as total_absent FROM attendance WHERE status = 'Absent'");
if ($result && $row = $result->fetch_assoc()) {
    $total_absent = $row['total_absent'];
}

// Fetch total number of late students
$result = $conn->query("SELECT COUNT(id) as total_late FROM attendance WHERE status = 'Late'");
if ($result && $row = $result->fetch_assoc()) {
    $total_late = $row['total_late'];
}

// Fetch notifications for the teacher
//$teacher_id = $_SESSION['teacher_id'];
//$notifications = $conn->query("SELECT * FROM notifications WHERE user_id = $teacher_id AND type = 'teacher' ORDER BY created_at DESC");

//while ($notification = $notifications->fetch_assoc()) {
 //   echo "<div class='notification-item'>" . $notification['message'] . "</div>";//
//}//

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: Arial, sans-serif;
        }

        .main-content {
            padding: 20px;
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .card h5, .card h6 {
            font-weight: bold;
        }

        .card-body {
            padding: 20px;
        }

        .announcement-container {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            height: 400px; /* Fixed height */
        }

        .announcement-section {
            flex: 0 0 60%;
            margin-right: 15px;
            background-color: #fff;
            border-radius: 10px;
            border: 1px solid #ddd;
            padding: 15px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            max-width: 60%; /* Fixed width for this section */
            box-sizing: border-box; /* Ensure padding is included in width calculation */
        }

        .scroll-container {
            position: relative;
            display: flex;
            align-items: center;
            height: calc(100% - 40px);
            max-width: 100%;
            overflow: hidden;
        }

        .scrollable-announcements {
            display: flex;
            overflow-x: auto;
            gap: 15px;
            padding: 10px 0;
            white-space: nowrap;
            scroll-behavior: smooth;
            height: 100%;
        }

        .scrollable-announcements::-webkit-scrollbar {
            display: none;
        }

        .announcement-card {
            display: inline-block;
            width: 150px; /* Adjusted for better fit */
            border: 1px solid #ddd;
            border-radius: 10px;
            overflow: hidden;
            background-color: #f8f9fc;
            white-space: normal;
            flex-shrink: 0; /* Prevent cards from shrinking */
            height: 70%; /* Make sure it fits within the container height */
            transition: transform 0.3s ease;
        }

        .announcement-card:hover {
            transform: scale(1.05);
        }

        .announcement-card img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-bottom: 1px solid #ddd;
        }

        .announcement-card h6 {
            margin: 10px;
            font-size: 1rem;
        }

        .announcement-card p {
            margin: 0 10px 10px;
            font-size: 0.875rem;
            color: #666;
        }

        .scroll-button {
            background-color: #007bff; /* Primary blue background */
            border: none;
            border-radius: 50%; /* Circular button */
            color: white; /* White arrow color */
            font-size: 24px;
            cursor: pointer;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            padding: 10px; /* Increased padding for larger buttons */
            transition: background-color 0.3s ease, transform 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Soft shadow for depth */
            z-index: 1; /* Ensure the button is on top of other content */
        }

        .scroll-button:hover {
            background-color: #0056b3; /* Darker blue on hover */
            transform: translateY(-50%) scale(1.1); /* Slightly enlarge on hover */
        }

        .scroll-button.left {
            left: 0; /* Align to the left */
        }

        .scroll-button.right {
            right: 0; /* Align to the right */
        }

        .scroll-button:focus {
            outline: none; /* Remove default focus outline */
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.5); /* Add a custom focus outline */
        }

        .extra-content {
            flex: 0 0 40%;
            background-color: #fff;
            border-radius: 10px;
            border: 1px solid #ddd;
            padding: 15px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            height: 100%;
        }

        .extra-content h5 {
            font-size: 1.2rem;
            margin-bottom: 15px;
        }

        .recent-activity ul {
            list-style: none;
            padding-left: 0;
            margin: 0;
        }

        .recent-activity li {
            margin-bottom: 10px;
            padding: 10px;
            background-color: #f8f9fc; /* Light background */
            border-radius: 8px; /* Rounded corners */
            border: 1px solid #ddd; /* Border for separation */
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .recent-activity li:hover {
            background-color: #e2e6ea; /* Slightly darker on hover */
            transform: translateY(-2px); /* Slight lift on hover */
        }

        .recent-activity a {
            text-decoration: none;
            color: #007bff;
            font-weight: 500;
            display: flex;
            align-items: center;
        }

        .recent-activity a:hover {
            text-decoration: underline;
        }

        .recent-activity a::before {
            content: "\f0da"; /* FontAwesome icon, like a chevron */
            font-family: 'FontAwesome';
            margin-right: 8px;
            color: #007bff;
        }

        @media (max-width: 768px) {
            .announcement-container {
                flex-direction: column;
                height: auto;
            }

            .announcement-section, .extra-content {
                flex: 1 1 100%;
                margin: 0 0 20px 0;
            }

            .scrollable-announcements {
                gap: 10px;
            }

            .announcement-card {
                width: 120px;
                height: auto;
            }
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; // Include the common layout ?>

<div class="main-content" id="main-content">
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
            <button class="btn btn-primary generate-report" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Generate Report">Generate Report</button>
        </div>

        <!-- Content Row -->
        <div class="row">
            <!-- Total Student Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Student</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_students; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Present Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Present</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_present; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Absent Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Absent</div>
                                <div class="row no-gutters align-items-center">
                                    <div class="col-auto">
                                        <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800"><?php echo $total_absent; ?>%</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Late Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Late</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_late; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="announcement-container">
            <!-- Announcement Section (60% width) -->
            <div class="announcement-section">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5>Posted Announcements</h5>
                    
                </div>

                <div class="scroll-container">
                    <button class="scroll-button left" onclick="scrollToLeft()">&#8249;</button>

                    <div class="scrollable-announcements" id="postedAnnouncements">
                        <!-- Example Announcement Cards -->
                        <?php 
                        $result = $conn->query("SELECT * FROM announcements WHERE status = 1");
                        while($row = $result->fetch_assoc()): ?>
                        <div class="announcement-card">
                            <img src="../uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="Announcement Image">
                            <div>
                                <h6><?php echo htmlspecialchars($row['title']); ?></h6>
                                <p><?php echo htmlspecialchars($row['posting_date']); ?></p>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <button class="scroll-button right" onclick="scrollToRight()">&#8250;</button>
                </div>
            </div>

            <!-- Extra Content Section (40% width) -->
            <div class="extra-content">
                <div>
                    <h5>Recent Activity</h5>
                    <div class="recent-activity">
                        <ul>
                            <li><a href="#">John Doe marked as present</a></li>
                            <li><a href="#">New announcement posted</a></li>
                            <!-- Add more recent activity items here -->
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="script3.js"></script>
<script>
    function scrollToLeft() {
        const postedAnnouncements = document.getElementById('postedAnnouncements');
        postedAnnouncements.scrollBy({
            left: -150,
            behavior: 'smooth'
        });
    }

    function scrollToRight() {
        const postedAnnouncements = document.getElementById('postedAnnouncements');
        postedAnnouncements.scrollBy({
            left: 150,
            behavior: 'smooth'
        });
    }

    $(document).ready(function() {
        // Handle Generate Report button click
        $('.generate-report').on('click', function() {
            // Navigate to the Reports tab in the Report module
            window.location.href = '/Capstone/teacher/Report.php#reports';
        });
    });
</script>
</body>
</html>
