<?php
session_start();

// Redirect non-admin users to the login page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../Login/login.php");
    exit();
}

include '../db.php';

// Fetch student data
if (isset($_GET['lrn'])) {
    $lrn = mysqli_real_escape_string($conn, $_GET['lrn']);
    $studentQuery = "SELECT * FROM students WHERE lrn = '$lrn' LIMIT 1";
    $result = $conn->query($studentQuery);

    if ($result && $result->num_rows > 0) {
        $student = $result->fetch_assoc();
    } else {
        echo "<script>alert('Student not found.'); window.location.href = 'students.php';</script>";
        exit();
    }
} else {
    header("Location: students.php");
    exit();
}

// Fetch grade levels and sections
$grade_levels = $conn->query("SELECT * FROM grade_levels");
$sections = $conn->query("SELECT * FROM sections");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .main-content {
            padding: 40px;
        }
        .form-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            max-width: 900px;
            margin: 0 auto;
        }
        .form-label {
            font-weight: bold;
            color: #333;
        }
        .btn-back {
            margin-bottom: 20px;
        }
        .header-title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<?php include 'navbar.php'; ?>

<div class="container main-content">
    <a href="students.php" class="btn btn-secondary btn-back"><i class="fas fa-arrow-left"></i> Back</a>
    
    <div class="form-container">
        <h1 class="header-title">Edit Student Profile</h1>

        <!-- Edit Student Form -->
        <form id="editStudentForm" enctype="multipart/form-data">
        

            <!-- Student Image Section -->
            <div class="row mb-3">
                <div class="col-md-12 text-center">
                    <label for="image" class="form-label">Student Image</label>
                    <div class="mb-2">
                        <!-- Display Current Image or Default Avatar -->
                        <img id="studentImagePreview" src="<?php echo !empty($student['image']) && file_exists('../' . $student['image']) ? '../' . htmlspecialchars($student['image']) : '../uploads/default-avatar.png'; ?>" alt="Student Image" style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%;">
                    </div>
                    <!-- File Upload Input -->
                    <input type="file" id="image" name="image" class="form-control" accept="image/*" onchange="previewImage(event)">
                    <button type="button" class="btn btn-danger mt-2" id="removeImage">Remove Image</button>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="lrn" class="form-label">LRN</label>
                    <input type="text" class="form-control" id="lrn" name="lrn" value="<?php echo htmlspecialchars($student['lrn']); ?>" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($student['full_name']); ?>" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="birth_date" class="form-label">Birth Date</label>
                    <input type="date" class="form-control" id="birth_date" name="birth_date" value="<?php echo htmlspecialchars($student['birth_date']); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="rfid_uid" class="form-label">RFID Tag</label>
                    <input type="text" class="form-control" id="rfid_uid" name="rfid_uid" value="<?php echo htmlspecialchars($student['rfid_uid']); ?>" readonly>
                </div>

            </div>

            <div class="row">
            <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" readonly>
            </div>
                <div class="col-md-6 mb-3">
                    <label for="contact" class="form-label">Contact</label>
                    <input type="text" class="form-control" id="contact" name="contact" value="<?php echo htmlspecialchars($student['contact']); ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="guardian" class="form-label">Guardian</label>
                    <input type="text" class="form-control" id="guardian" name="guardian" value="<?php echo htmlspecialchars($student['guardian']); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="address" class="form-label">Address</label>
                    <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($student['address']); ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="sex" class="form-label">Sex</label>
                    <select class="form-control" id="sex" name="sex">
                        <option value="Male" <?php if ($student['sex'] === 'Male') echo 'selected'; ?>>Male</option>
                        <option value="Female" <?php if ($student['sex'] === 'Female') echo 'selected'; ?>>Female</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-control" id="status" name="status">
                        <option value="1" <?php if ($student['status'] == 1) echo 'selected'; ?>>Active: The student is enrolled and active.</option>
                        <option value="0" <?php if ($student['status'] == 0) echo 'selected'; ?>>Inactive: The student is not currently enrolled or active.</option>
                        <option value="2" <?php if ($student['status'] == 2) echo 'selected'; ?>>Pending: Awaiting administrative approval.</option>
                        <option value="3" <?php if ($student['status'] == 3) echo 'selected'; ?>>Rejected: Application or enrollment rejected.</option>
                    </select>
                </div>

            </div>

            <div class="row">
                <!-- Grade Level Dropdown -->
            <div class="col-md-6 mb-3">
                <label for="grade_level_id" class="form-label">Grade Level</label>
                <select class="form-control" id="grade_level_id" name="grade_level_id">
                    <option value="">Select a Grade Level</option>
                    <?php while ($gl = $grade_levels->fetch_assoc()): ?>
                        <option value="<?php echo $gl['id']; ?>" <?php echo ($gl['id'] == $student['grade_level_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($gl['grade_level']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Section Dropdown -->
            <div class="col-md-6 mb-3">
                <label for="section_id" class="form-label">Section</label>
                <select class="form-control" id="section_id" name="section_id">
                    <option value="">Select Section</option>
                    <?php while ($sec = $sections->fetch_assoc()): ?>
                        <option value="<?php echo $sec['id']; ?>" <?php echo ($sec['id'] == $student['section_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($sec['section']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            </div>

            <button type="submit" class="btn btn-primary w-100">Update Student</button>
        </form>
    </div>
</div>
<!-- Feedback Modal -->
<div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="feedbackModalLabel">Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="feedbackMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- jQuery and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Preview Image
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const output = document.getElementById('studentImagePreview');
            output.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }

    // Remove Image Button
    document.getElementById('removeImage').addEventListener('click', function() {
        document.getElementById('studentImagePreview').src = '../uploads/default-avatar.png';
        document.getElementById('image').value = '';
    });

    // AJAX form submission
    $('#editStudentForm').on('submit', function(event) {
    event.preventDefault();
    
    var formData = new FormData(this);

    $.ajax({
        url: 'process_edit_student.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            var res = JSON.parse(response);
            $('#feedbackMessage').text(res.message);
            $('#feedbackModal').modal('show');

            if (res.status === 'success') {
                $('#feedbackModal').on('hidden.bs.modal', function () {
                    window.location.href = 'students.php';
                });
            }
        },
        error: function(xhr, status, error) {
            $('#feedbackMessage').text('An error occurred while updating the student.');
            $('#feedbackModal').modal('show');
        }
    });
});

</script>
</body>
</html>
