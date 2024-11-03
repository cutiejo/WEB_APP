<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: ../Login/login.php");
    exit();
}
include '../db.php';

$teacher_id = $_SESSION['user_id'];
$query = "SELECT * FROM teachers WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $teacher = $result->fetch_assoc();
} else {
    echo "Teacher not found!";
    exit;
}

$success = false;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $rfid_tag = $_POST['rfid_tag'];

    // Handle image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $target_dir = "../uploads/";
        $target_file = $target_dir . basename($_FILES["profile_image"]["name"]);
        move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file);
        $image_path = $target_file;
    } else {
        $image_path = $teacher['image']; // Use existing image if no new image is uploaded
    }

    $update_query = "UPDATE teachers SET full_name = ?, email = ?, phone = ?, rfid_uid = ?, image = ? WHERE user_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("sssssi", $full_name, $email, $phone, $rfid_tag, $image_path, $teacher_id);
    if ($update_stmt->execute()) {
        $success = true;
    } else {
        echo "<script>alert('Failed to update profile');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }
        .profile-left, .profile-right {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .profile-left {
            flex: 1;
            text-align: center;
        }
        .profile-left img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(0, 0, 0, 0.1);
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        }
        .profile-left h2 {
            margin-top: 10px;
        }
        .profile-left p {
            margin-top: 5px;
            color: #555;
        }
        .profile-right {
            flex: 1;
            display: none;
        }
        .save-btn {
            text-align: right;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- Main Content -->
    <div class="main-content" id="main-content">
        <div class="container">
            <div class="profile-container">
                <!-- Profile Display -->
                <div class="profile-left">
                    <img id="profile-image" src="<?php echo htmlspecialchars($teacher['image'] ?? '../assets/imgs/user.svg'); ?>" alt="Teacher Image">
                    <h2><?php echo htmlspecialchars($teacher['full_name']); ?></h2>
                    <p>Email: <?php echo htmlspecialchars($teacher['email']); ?></p>
                    <p>Phone: <?php echo htmlspecialchars($teacher['phone'] ?? 'Not provided'); ?></p>
                    <p>RFID Tag: <?php echo htmlspecialchars($teacher['rfid_uid'] ?? 'Not assigned'); ?></p>
                    <button id="editProfileBtn" class="btn btn-success mt-3"><i class="fas fa-edit"></i> Edit Profile</button>
                </div>

                <!-- Profile Edit Form -->
                <div class="profile-right" id="editProfile">
                    <h3>Edit Profile</h3>
                    <form method="POST" action="profile.php" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($teacher['full_name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($teacher['email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($teacher['phone'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="rfid_tag" class="form-label">RFID Tag</label>
                            <input type="text" class="form-control" name="rfid_tag" value="<?php echo htmlspecialchars($teacher['rfid_uid'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="profile_image" class="form-label">Profile Image</label>
                            <input type="file" class="form-control" name="profile_image">
                            <?php if ($teacher['image']): ?>
                                <img src="<?php echo htmlspecialchars($teacher['image']); ?>" alt="Profile Image" style="width:100px; height:100px; margin-top:10px;">
                            <?php endif; ?>
                        </div>

                        <div class="save-btn">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="successModalLabel">Success</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Profile updated successfully!
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('editProfileBtn').addEventListener('click', function() {
            document.getElementById('editProfile').style.display = 'block';
        });

        <?php if ($success): ?>
        $(document).ready(function() {
            $('#successModal').modal('show');
        });
        <?php endif; ?>
    </script>
</body>
</html>
