<?php
session_start();

// Redirect non-admin users to the login page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../Login/login.php");
    exit();
}

include '../db.php';

// Fetch teacher data
if (isset($_GET['id'])) {
    $teacherId = mysqli_real_escape_string($conn, $_GET['id']);
    $teacherQuery = "SELECT * FROM teachers WHERE id = '$teacherId' LIMIT 1";
    $result = $conn->query($teacherQuery);

    if ($result && $result->num_rows > 0) {
        $teacher = $result->fetch_assoc();
    } else {
        echo "<script>alert('Teacher not found.'); window.location.href = 'teacher_management.php';</script>";
        exit();
    }
} else {
    header("Location: teacher_management.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Teacher Profile</title>
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
    <a href="teacher_management.php" class="btn btn-secondary btn-back"><i class="fas fa-arrow-left"></i> Back</a>
    
    <div class="form-container">
        <h1 class="header-title">Edit Teacher Profile</h1>

        <!-- Edit Teacher Form -->
        <form id="editTeacherForm" enctype="multipart/form-data">
        
            <!-- Teacher Image Section -->
            <div class="row mb-3">
                <div class="col-md-12 text-center">
                    <label for="image" class="form-label">Teacher Image</label>
                    <div class="mb-2">
                        <!-- Display Current Image or Default Avatar -->
                        <img id="teacherImagePreview" src="<?php echo !empty($teacher['image']) && file_exists('../' . $teacher['image']) ? '../' . htmlspecialchars($teacher['image']) : '../uploads/default-avatar.png'; ?>" alt="Teacher Image" style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%;">
                    </div>
                    <!-- File Upload Input -->
                    <input type="file" id="image" name="image" class="form-control" accept="image/*" onchange="previewImage(event)">
                    <button type="button" class="btn btn-danger mt-2" id="removeImage">Remove Image</button>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($teacher['full_name']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($teacher['email']); ?>" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($teacher['phone']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="rfid_uid" class="form-label">RFID Tag</label>
                    <input type="text" class="form-control" id="rfid_uid" name="rfid_uid" value="<?php echo htmlspecialchars($teacher['rfid_uid']); ?>" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100">Update Teacher</button>
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
            const output = document.getElementById('teacherImagePreview');
            output.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }

    // Remove Image Button
    document.getElementById('removeImage').addEventListener('click', function() {
        document.getElementById('teacherImagePreview').src = '../uploads/default-avatar.png';
        document.getElementById('image').value = '';
    });

    // AJAX form submission
    $('#editTeacherForm').on('submit', function(event) {
    event.preventDefault();
    
    var formData = new FormData(this);

    $.ajax({
        url: 'process_edit_teacher.php',
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
                    window.location.href = 'teacher_management.php';
                });
            }
        },
        error: function(xhr, status, error) {
            $('#feedbackMessage').text('An error occurred while updating the teacher.');
            $('#feedbackModal').modal('show');
        }
    });
});

</script>
</body>
</html>
