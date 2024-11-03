<?php
session_start(); // Start the session

include '../db.php';

// Check if the user is logged in and has the 'teacher' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    echo "Teacher not logged in or session expired!";
    exit;
}

$teacher_id = $_SESSION['user_id']; // Use the session-stored user_id

// Fetch teacher details from the database
$query = "SELECT * FROM users WHERE id = ? AND role = 'teacher'";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error); // Debugging line to check SQL errors
}
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if the teacher exists
if ($result && $result->num_rows > 0) {
    $teacher = $result->fetch_assoc();
} else {
    echo "Teacher not found!";
    exit;
}

$success = false;

// Handle password update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['currentPassword']) && isset($_POST['newPassword'])) {
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];

    // Verify the current password
    if (password_verify($currentPassword, $teacher['password'])) {
        // Hash the new password
        $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update teacher password in the database
        $update_query = "UPDATE users SET password = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        if (!$update_stmt) {
            die("Error preparing statement: " . $conn->error); // Debugging line to check SQL errors
        }
        $update_stmt->bind_param("si", $hashedNewPassword, $teacher_id);

        if ($update_stmt->execute()) {
            $success = true; // Set success flag to true
            echo "<script>alert('Password updated successfully');</script>";
        } else {
            echo "<script>alert('Failed to update password');</script>";
        }
    } else {
        echo "<script>alert('Current password is incorrect');</script>";
    }
}

// Check if the user is logged in and has the 'teacher' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    echo "Teacher not logged in or session expired!";
    exit;
}

$teacher_id = $_SESSION['user_id']; // Use the session-stored user_id

// Fetch teacher details from the database
$query = "SELECT * FROM users WHERE id = ? AND role = 'teacher'";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $teacher = $result->fetch_assoc();
} else {
    echo "Teacher not found!";
    exit;
}

// Initialize the assignment text
$current_assignments_text = "No current assignment available";

// Fetch the teacher's grade and section assignment from teacher_assignments
$assignment_query = "SELECT grade_level_id, section_id 
                     FROM teacher_assignments
                     WHERE teacher_id = ?";
$assignment_stmt = $conn->prepare($assignment_query);
if ($assignment_stmt) {
    $assignment_stmt->bind_param("i", $teacher_id);
    $assignment_stmt->execute();
    $assignment_result = $assignment_stmt->get_result();
    if ($assignment_result && $assignment_result->num_rows > 0) {
        $assignment = $assignment_result->fetch_assoc();
        $current_assignments_text = "Grade Level ID: " . $assignment['grade_level_id'] . ", Section ID: " . $assignment['section_id'];
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
    <!-- Custom CSS -->
    <style>
        /* Basic styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
        }

        .container-fluid {
            display: flex;
            height: calc(100vh - 80px);
            margin-top: 20px;
        }

        /* Sidebar styling */
        .content-sidebar {
            width: 60px;
            background-color: #fff;
            padding: 10px;
            border-right: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
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

        /* Main content area */
        .main-content1 {
            flex-grow: 1;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
            margin-left: 10px;
        }

        /* Header styling */
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

        /* Panel visibility control */
        .settings-panel {
            display: none;
        }

        .settings-panel.active {
            display: block;
        }

        /* Card styling */
        .card {
    width: 100%;
    padding: 20px;
    border-radius: 10px;
    background-color: transparent;
    box-shadow: none;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
}
        

        .card h5 {
            margin-bottom: 20px;
            font-size: 1.25rem;
            font-weight: bold;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
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

   /* Ensure the Additional Information card does not respond to hover */
#rfidDigit {
    transition: none; /* Remove transition effect */
    opacity: 100; /* Dim the appearance */
    pointer-events: none; /* Make it non-interactive */
}

/* Disable any hover effect specifically on this card */
#rfidDigit:hover {
    transform: none; /* Disable scale or transform effects */
    box-shadow: none; /* Remove shadow change on hover */
    opacity: 0.6; /* Ensure opacity doesn't change on hover */
}


    </style>
</head>
<body>
    <!-- Navbar -->
    <?php include 'navbar.php'; ?>

    <!-- Main Content -->
    <div class="main-content" id="main-content">
        <div class="container-fluid">
            <!-- Content Sidebar -->
            <div class="content-sidebar" id="sidebar">
                <ul>
                    <li><a href="#" data-panel="smsNotification" data-title="Settings" class="active"><i class="fa-solid fa-message"></i></a></li>
                    <li><a href="#" data-panel="rfidDigit" data-title="Grade and Section"><i class="fas fa-sliders-h"></i></a></li>
                </ul>
            </div>

           <!-- Main Content Area -->
<div class="main-content1" id="mainContent">
    <div class="settings-header">
        <h3 id="headerTitle">Settings</h3> <!-- Header with id for dynamic update -->
    </div>

    <!-- Change Password Panel -->
    <div class="settings-panel active" id="smsNotification">
        <h5>Change Password</h5>
        <form action="" method="POST">
            <div class="mb-3">
                <label for="currentPassword" class="form-label">Enter Current Password</label>
                <input type="password" class="form-control" id="currentPassword" name="currentPassword" required>
            </div>
            <div class="mb-3">
                <label for="newPassword" class="form-label">Enter New Password</label>
                <input type="password" class="form-control" id="newPassword" name="newPassword" required>
            </div>
            <div class="mb-3">
                <label for="confirmPassword" class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>

    <!-- Grade and Section Panel -->
    <div class="settings-panel" id="rfidDigit">
        <div class="card">
            
            <p><strong>Current Assignment:</strong> <?= htmlspecialchars($current_assignments_text); ?></p>
        </div>
    </div>
</div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script to switch between panels and update header title -->
    <script>
    $(document).ready(function(){
        $('.content-sidebar ul li a').click(function(e){
            e.preventDefault();
            $('.content-sidebar ul li a').removeClass('active');
            $(this).addClass('active');

            // Show the relevant panel
            var panelToShow = $(this).data('panel');
            $('.settings-panel').removeClass('active');
            $('#' + panelToShow).addClass('active');

            // Update the header title based on data-title attribute
            var newTitle = $(this).data('title');
            $('#headerTitle').text(newTitle);
        });

        // JavaScript for confirming password match
        document.querySelector("form").addEventListener("submit", function(e) {
            const newPassword = document.getElementById("newPassword").value;
            const confirmPassword = document.getElementById("confirmPassword").value;
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert("New Password and Confirm Password do not match.");
            }
        });
    });
    </script>
</body>
</html>
