<?php
include '../db.php';

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../Login/login.php");
    exit();
}

$teacher_id = isset($_GET['teacher_id']) ? $_GET['teacher_id'] : null;

if ($teacher_id === null) {
    echo "Invalid request. Teacher ID is missing.";
    exit();
}

// Fetch current assignments for the teacher
$current_assignments_query = $conn->prepare("
    SELECT g.grade_level, s.section
    FROM teacher_assignments ta
    LEFT JOIN grade_levels g ON ta.grade_level_id = g.id
    LEFT JOIN sections s ON ta.section_id = s.id
    WHERE ta.teacher_id = ?
");
$current_assignments_query->bind_param("i", $teacher_id);
$current_assignments_query->execute();
$current_assignments_result = $current_assignments_query->get_result();
$current_assignments = [];
while ($row = $current_assignments_result->fetch_assoc()) {
    $current_assignments[] = $row['grade_level'] . ' - ' . $row['section'];
}
$current_assignments_text = implode(', ', $current_assignments);

// Fetch all grade levels and sections for the dropdowns
$grade_levels_result = $conn->query("SELECT * FROM grade_levels");
$sections_result = $conn->query("SELECT * FROM sections");

// Handling the form submission for assigning grade and section
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $grade_level_id = $_POST['grade_level_id'];  // Matches the form field name
    $section_id = $_POST['section_id'];          // Matches the form field name

    if (!empty($grade_level_id) && !empty($section_id)) {
        // Insert into the `teacher_assignments` table
        $insert_assignments_query = $conn->prepare("
            INSERT INTO teacher_assignments (teacher_id, grade_level_id, section_id)
            VALUES (?, ?, ?)
        ");
        $insert_assignments_query->bind_param("iii", $teacher_id, $grade_level_id, $section_id);
        $insert_assignments_query->execute();

        // Update the `teachers` table with the assigned grade level and section
        $update_teachers_query = $conn->prepare("
            UPDATE teachers
            SET grade_level_id = ?, section_id = ?
            WHERE id = ?
        ");
        $update_teachers_query->bind_param("iii", $grade_level_id, $section_id, $teacher_id);
        $update_teachers_query->execute();

        if ($conn->affected_rows > 0) {
            // Provide success feedback
            header("Location: assigning_grade_section.php?teacher_id=$teacher_id&success=1");
            exit();
        } else {
            echo "Failed to update teacher.";
        }
    } else {
        echo "Grade level or section is missing.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign/Unassign Grade & Section</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .form-container {
            display: flex;
            justify-content: space-between;
        }
        .form-container .form-box {
            width: 49%; /* Adjust width to fit two columns side by side */
            padding: 15px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .alert-success {
            display: none; /* Hidden by default, shown when assignment is successful */
        }
        .back-button {
            margin-bottom: 10px;
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
                <h1 class="h3 mb-0 text-gray-800">Assign/Unassign Grade & Section</h1>
            </div>

            <!-- Back Button -->
            <a href="teacher_management.php" class="btn btn-secondary back-button"><i class="fas fa-arrow-left"></i> Back</a>

            <!-- Success Indicator -->
            <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                <div class="alert alert-success" role="alert">
                    Grade and section successfully assigned!
                </div>
            <?php endif; ?>

            <!-- Display Current Assignments -->
            <div class="alert alert-info">
                <strong>Currently Assigned:</strong> <?= $current_assignments_text ? $current_assignments_text : 'No current assignments.'; ?>
            </div>

            <!-- Form Container: Left (Assigning) and Right (Unassigning) -->
            <div class="form-container">

                <!-- Assigning Form -->
                <div class="form-box">
                    <h4>Assign Grade and Section</h4>
                    <form id="assignGradeSectionForm" method="POST" action="">
                        <input type="hidden" name="teacher_id" value="<?= htmlspecialchars($teacher_id); ?>">

                        <!-- Grade Level Dropdown -->
                        <div class="form-group mb-3">
                            <label for="grade_level">Assign New Grade Level</label>
                            <select class="form-control" id="grade_level" name="grade_level_id">
                                <option value="">Select Grade Level</option>
                                <?php while ($grade = $grade_levels_result->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($grade['id']); ?>"><?= htmlspecialchars($grade['grade_level']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Section Dropdown -->
                        <div class="form-group mb-3">
                            <label for="section">Assign New Section</label>
                            <select class="form-control" id="section" name="section_id">
                                <option value="">Select Section</option>
                                <?php while ($section = $sections_result->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($section['id']); ?>"><?= htmlspecialchars($section['section']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary">Assign</button>
                    </form>
                </div>

                <!-- Unassigning Form -->
                <div class="form-box">
                    <h4>Unassign Grade and Section</h4>
                    <form id="unassignGradeSectionForm" method="POST" action="unassign_grade_section.php">
                        <input type="hidden" name="teacher_id" value="<?= htmlspecialchars($teacher_id); ?>">

                        <!-- Grade Level Unassign Dropdown -->
                        <div class="form-group mb-3">
                            <label for="unassign_grade_level">Unassign Grade Level</label>
                            <select class="form-control" id="unassign_grade_level" name="unassign_grade_level_id">
                                <option value="">Select Grade Level to Unassign</option>
                                <?php foreach ($current_assignments as $assignment): ?>
                                    <?php
                                    list($grade_level, $section) = explode(' - ', $assignment);
                                    ?>
                                    <option value="<?= htmlspecialchars($grade_level); ?>"><?= htmlspecialchars($assignment); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Section Unassign Dropdown -->
                        <div class="form-group mb-3">
                            <label for="unassign_section">Unassign Section</label>
                            <select class="form-control" id="unassign_section" name="unassign_section_id">
                                <option value="">Select Section to Unassign</option>
                                <?php foreach ($current_assignments as $assignment): ?>
                                    <?php
                                    list($grade_level, $section) = explode(' - ', $assignment);
                                    ?>
                                    <option value="<?= htmlspecialchars($section); ?>"><?= htmlspecialchars($assignment); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-danger">Unassign</button>
                    </form>
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
