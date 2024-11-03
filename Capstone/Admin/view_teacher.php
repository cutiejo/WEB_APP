<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../Login/login.php"); // Redirect to login if not an admin
    exit();
}
include '../db.php';

// Get teacher ID from URL
$teacher_id = isset($_GET['id']) ? $_GET['id'] : '';

if (empty($teacher_id)) {
    echo 'No teacher specified.';
    exit;
}

// SQL query to fetch teacher details
$sql = "SELECT teachers.id, teachers.full_name, teachers.email, teachers.phone, teachers.rfid_uid, teachers.image, 
        grade_levels.grade_level AS assigned_grade_level, sections.section AS assigned_section 
        FROM teachers 
        LEFT JOIN grade_levels ON teachers.grade_level_id = grade_levels.id 
        LEFT JOIN sections ON teachers.section_id = sections.id 
        WHERE teachers.id = ?";

$stmt = $conn->prepare($sql);

// Check if the SQL statement prepared successfully
if ($stmt === false) {
    die("SQL error: " . $conn->error);
}

// Bind the parameter to the SQL query
$stmt->bind_param('i', $teacher_id); // 'i' stands for integer (assuming teacher_id is an integer)
$stmt->execute();
$result = $stmt->get_result();

// Check if a teacher is found
if ($result->num_rows === 0) {
    echo 'Teacher not found.';
    exit;
}

$row = $result->fetch_assoc();
$teacher_name = $row['full_name'];
$email = $row['email'];
$phone = $row['phone'];
$rfid_uid = $row['rfid_uid'];
$image = $row['image'];
$grade_level_name = $row['assigned_grade_level'];
$section_name = $row['assigned_section'];

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Profile</title>
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
        .profile-card h3 {
            margin-top: 10px;
            font-size: 16px;
            font-weight: bold;
        }
        .profile-card h6 {
            text-align: left; 
            width: 100%;
            margin-left: 15px;
            font-size: 16px;
            color: #333;
            font-weight: bold;
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
                <h1 class="h3 mb-0 text-gray-800">Teacher Profile</h1>
            </div>

            <!-- Back Button -->
            <a href="teacher_management.php" class="btn btn-secondary back-button"><i class="fas fa-arrow-left"></i> Back</a>

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
                            <h3><?php echo htmlspecialchars($teacher_name); ?></h3>
                            <hr class="fixed-hr">
                            <h6>Teacher ID: <?php echo htmlspecialchars($teacher_id); ?></h6>
                            <h6>Section: <?php echo htmlspecialchars($section_name); ?></h6>
                            <h6>Grade Level: <?php echo htmlspecialchars($grade_level_name); ?></h6>
                        </div>
                    </div>

                    <!-- Medium White Box (Second Box) -->
                    <div class="white-box-medium">
                        <div class="profile-card-two-value">
                            <h6 style="font-size: 16px; font-weight: bold; color: #333; margin-bottom: 10px;">
                                <i class="fas fa-info-circle"></i> GENERAL INFO
                            </h6>
                            <div>
                                <div class="label">Teacher ID:</div>
                                <div class="value"><?php echo htmlspecialchars($teacher_id); ?></div>
                            </div>
                            <div>
                                <div class="label">Email:</div>
                                <div class="value"><?php echo htmlspecialchars($email); ?></div>
                            </div>
                            <div>
                                <div class="label">Phone:</div>
                                <div class="value"><?php echo htmlspecialchars($phone); ?></div>
                            </div>
                            <div>
                                <div class="label">RFID TAG:</div>
                                <div class="value"><?php echo htmlspecialchars($rfid_uid); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
