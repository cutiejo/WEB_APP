<?php
session_start(); // Start the session

include '../db.php';

// Check if the user is logged in and has the 'admin' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    echo "Admin not logged in or session expired!";
    exit;
}

$admin_id = $_SESSION['user_id']; // Use the session-stored user_id

// Fetch admin details from the database
$query = "SELECT * FROM users WHERE id = ? AND role = 'admin'";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error); // Debugging line to check SQL errors
}
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if the admin exists
if ($result && $result->num_rows > 0) {
    $admin = $result->fetch_assoc();
} else {
    echo "Admin not found!";
    exit;
}

$success = false;

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the new password

    // Update admin details in the database
    $update_query = "UPDATE users SET full_name = ?, email = ?, password = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    if (!$update_stmt) {
        die("Error preparing statement: " . $conn->error); // Debugging line to check SQL errors
    }
    $update_stmt->bind_param("sssi", $full_name, $email, $password, $admin_id);

    if ($update_stmt->execute()) {
        $success = true; // Set success flag to true
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
    <title>Admin Profile</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
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
        .form-group {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            top: 75%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
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
                    <img id="profile-image" src="../assets/imgs/adminpic.png" alt="Admin Image">
                    <h2><?php echo htmlspecialchars($admin['full_name']); ?></h2>
                    <p>Email: <?php echo htmlspecialchars($admin['email']); ?></p>
                    
                    <button id="editProfileBtn" class="btn btn-success mt-3"><i class="fas fa-edit"></i> Edit Profile</button>
                </div>

                <!-- Profile Edit Form -->
                <div class="profile-right" id="editProfile">
                    <h3>Edit Profile</h3>
                    <form method="POST" action="profile.php">
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($admin['full_name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                        </div>
                        <div class="mb-3 form-group">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" id="password" required>
                            <span class="password-toggle" onclick="togglePassword('password')"><i class="fas fa-eye"></i></span>
                        </div>
                        <div class="mb-3 form-group">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" name="confirm_password" id="confirm_password" required>
                            <span class="password-toggle" onclick="togglePassword('confirm_password')"><i class="fas fa-eye"></i></span>
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script3.js"></script>
    <script>
        document.getElementById('editProfileBtn').addEventListener('click', function() {
            document.getElementById('editProfile').style.display = 'block';
        });

        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const passwordToggle = passwordField.nextElementSibling.querySelector('i');
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            passwordToggle.classList.toggle('fa-eye-slash');
        }

        <?php if ($success): ?>
        $(document).ready(function() {
            $('#successModal').modal('show');
        });
        <?php endif; ?>
    </script>
</body>
</html>
