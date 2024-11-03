<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../Login/login.php");
    exit();
}

include '../db.php';

// Get LRN from URL
$lrn = isset($_GET['lrn']) ? $_GET['lrn'] : '';

if (empty($lrn)) {
    echo 'No student specified.';
    exit;
}

// Fetch student details based on LRN
$sql = "SELECT students.lrn, students.rfid_uid, students.full_name, students.address, students.sex, students.image, 
        grade_levels.grade_level, sections.section, parents.full_name AS parent_name, parents.phone AS parent_phone, parents.address AS parent_address
        FROM students
        LEFT JOIN grade_levels ON students.grade_level_id = grade_levels.id
        LEFT JOIN sections ON students.section_id = sections.id
        LEFT JOIN parents ON students.parent_id = parents.parent_id
        WHERE students.lrn = ?";

// Prepare the statement
$stmt = $conn->prepare($sql);

// Check if the statement was prepared correctly
if (!$stmt) {
    die("Error preparing the SQL statement: " . $conn->error);
}

$stmt->bind_param('s', $lrn);
$stmt->execute();
$result = $stmt->get_result();

// Check if a student is found
if ($result->num_rows === 0) {
    echo 'No matching student found with LRN: ' . htmlspecialchars($lrn);
    exit;
}

$row = $result->fetch_assoc();
$rfid_uid = $row['rfid_uid'];
$student_name = $row['full_name'];
$address = $row['address'];
$sex = $row['sex'];
$image = $row['image'];
$grade_level_name = $row['grade_level'];
$section_name = $row['section'];
$parent_name = isset($row['parent_name']) ? $row['parent_name'] : 'N/A';
$parent_phone = isset($row['parent_phone']) ? $row['parent_phone'] : 'N/A';
$parent_address = isset($row['parent_address']) ? $row['parent_address'] : 'N/A';

$stmt->close();
$conn->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <!-- Bootstrap CSS and other styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Styling for content */
        .main-content {
            display: flex;
            flex-wrap: wrap;
            height: calc(100vh - 86px);
            overflow-y: auto;
        }
        .white-box-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .first-row {
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }
        .white-box-small {
            width: 25%;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .white-box-medium {
            flex: 1;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            overflow-y: visible;
        }
        .profile-card {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .profile-card img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #007bff;
        }
        
        .profile-card h3, .profile-card h6 {
            font-size: 16px;
            color: #333;
            margin-top: 10px;
           
        }

        .profile-card .label {
            font-weight: bold;
        }

        .profile-card .value {
            font-weight: normal;
        }

        .profile-card-two-value {
            display: table;
            width: 95%;
            border-collapse: collapse;
            font-size: 13px;
            font-weight: bold;
            margin: 15px;
        }
        .profile-card-two-value div {
            display: table-row;
        }
        .profile-card-two-value .label,
        .profile-card-two-value .value {
            display: table-cell;
            padding: 10px 15px;
            border: 1px solid #ddd;
        }
        .profile-card-two-value .label {
            width: 25%;
            text-align: left;
        }
        .profile-card-two-value .value {
            text-align: left;
            font-weight: normal;
        }
        /* Back Button */
        .back-button {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php include 'navbar.php'; ?>

    <!-- Main Content -->
    <div class="main-content" id="main-content">
        <div class="container-fluid">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Student Profile</h1>
            </div>

            <!-- Back Button -->
            <a href="students.php" class="btn btn-secondary back-button"><i class="fas fa-arrow-left"></i> Back</a>

            <!-- White Box (Parent Container) -->
            <div class="white-box-container">
                <div class="first-row">
                    <!-- Small White Box (Profile Card) -->
                    <div class="white-box-small">
                        <div class="profile-card">
                            <!-- Display the image stored in the database -->
                            <?php if (!empty($image) && file_exists('../' . $image)): ?>
                                <img src="<?php echo '../' . htmlspecialchars($image); ?>" alt="Profile Picture">
                            <?php else: ?>
                                <img src="../uploads/default-avatar.png" alt="Profile Picture">
                            <?php endif; ?>
                            <h3><span class="label">Full Name:</span> <span class="value"><?php echo htmlspecialchars($student_name); ?></span></h3>
                            <hr class="fixed-hr">
                            <h6><span class="label">LRN:</span> <span class="value"><?php echo htmlspecialchars($lrn); ?></span></h6>
                            <h6><span class="label">Section:</span> <span class="value"><?php echo htmlspecialchars($section_name); ?></span></h6>
                            <h6><span class="label">Grade Level:</span> <span class="value"><?php echo htmlspecialchars($grade_level_name); ?></span></h6>
                        </div>
                    </div>

                    <!-- Medium White Box (Student Info) -->
                    <div class="white-box-medium">
                        <div class="profile-card-two-value">
                            <h6 style="font-size: 16px; font-weight: bold; color: #333; margin-bottom: 10px;">
                                <i class="fas fa-info-circle"></i> GENERAL INFO
                            </h6>
                            <div>
                                <div class="label">LRN:</div>
                                <div class="value"><?php echo htmlspecialchars($lrn); ?></div>
                            </div>
                            <div>
                                <div class="label">RFID TAG:</div>
                                <div class="value"><?php echo htmlspecialchars($rfid_uid); ?></div>
                            </div>
                            <div>
                                <div class="label">ADDRESS:</div>
                                <div class="value"><?php echo htmlspecialchars($address); ?></div>
                            </div>
                            
                            <div>
                                <div class="label">SEX:</div>
                                <div class="value"><?php echo htmlspecialchars($sex); ?></div>
                            </div>
                            <h6 style="font-size: 16px; font-weight: bold; color: #333; margin-top: 20px;">
                                <i class="fas fa-user"></i> PARENT INFO
                            </h6>
                            <div>
                                <div class="label">Parent Name:</div>
                                <div class="value"><?php echo htmlspecialchars($parent_name); ?></div>
                            </div>
                            <div class="label">Parent Contact:</div>
                            <div class="value"><?php echo htmlspecialchars($parent_phone); ?></div>
                            <div>
                                <div class="label">Parent Address:</div>
                                <div class="value"><?php echo htmlspecialchars($parent_address); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
