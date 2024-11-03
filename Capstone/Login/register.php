<?php
include '../db.php';  // Ensure this file connects to your database
include 'functions.php';  // If you have helper functions here, keep it

$error_message = '';
$registration_success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? '';
    $lrn = $_POST['lrn'] ?? '';

    // Validate roles to include admin, teacher, student, and parent
    if (!in_array($role, ['admin', 'teacher', 'student', 'parent'])) {
        $error_message = 'Invalid role selected!';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Passwords do not match!';
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if the email already exists in the users table
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        if (!$stmt) {
            die("Prepare failed for checking existing email: " . $conn->error);
        }
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = 'Email already registered!';
        } else {
            // If the role is student, check if LRN already exists
            if ($role === 'student') {
                $stmt = $conn->prepare("SELECT * FROM students WHERE lrn = ?");
                if (!$stmt) {
                    die("Prepare failed for checking existing LRN: " . $conn->error);
                }
                $stmt->bind_param('s', $lrn);
                $stmt->execute();
                $result_lrn = $stmt->get_result();

                if ($result_lrn->num_rows > 0) {
                    $error_message = 'LRN already registered!';
                } else {
                    // Continue to insert the user and student records
                    registerUserAndStudent($conn, $full_name, $email, $hashed_password, $role, $lrn, $_POST);
                    $registration_success = true;  // Set success flag after successful registration
                }
            } else {
                // For other roles (admin, teacher, parent), insert directly
                registerUser($conn, $full_name, $email, $hashed_password, $role, $_POST);
                $registration_success = true;  // Set success flag after successful registration
            }
        }
        $stmt->close();
    }

    // Close the database connection
    $conn->close();
}

function registerUser($conn, $full_name, $email, $hashed_password, $role, $post_data) {
    // Insert the new user into the users table
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed for inserting user: " . $conn->error);
    }
    $stmt->bind_param("ssss", $full_name, $email, $hashed_password, $role);
    if ($stmt->execute()) {
        // Fetch the inserted user's ID
        $user_id = $conn->insert_id;

        if ($role === 'teacher') {
            // Insert into the teachers table for teachers
            $phone = $post_data['phone'] ?? '';
            $rfid_uid = $post_data['rfid_uid'] ?? '';
            $grade_level_id = $post_data['grade_level_id'] ?? null;
            $section_id = $post_data['section_id'] ?? null;

            $stmt_teacher = $conn->prepare("INSERT INTO teachers (user_id, full_name, email, phone, rfid_uid, grade_level_id, section_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt_teacher) {
                die("Prepare failed for inserting teacher: " . $conn->error);
            }
            $stmt_teacher->bind_param('issssii', $user_id, $full_name, $email, $phone, $rfid_uid, $grade_level_id, $section_id);
            $stmt_teacher->execute();
            $stmt_teacher->close();
        }
    } else {
        die('Execute failed: ' . $stmt->error);
    }
    $stmt->close();
}

function registerUserAndStudent($conn, $full_name, $email, $hashed_password, $role, $lrn, $post_data) {
    // Insert into the users table
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed for inserting user: " . $conn->error);
    }
    $stmt->bind_param("ssss", $full_name, $email, $hashed_password, $role);
    if ($stmt->execute()) {
        // Fetch the inserted user's ID
        $user_id = $conn->insert_id;

        // Insert into the students table with status set to pending (0)
        $birth_date = $post_data['birth_date'] ?? '';
        $rfid_uid = $post_data['rfid_uid'] ?? '';
        $age = $post_data['age'] ?? 0;
        $address = $post_data['address'] ?? '';
        $sex = $post_data['sex'] ?? '';
        $guardian = $post_data['guardian'] ?? '';
        $contact = $post_data['contact'] ?? '';
        $grade_level_id = $post_data['grade_level_id'] ?? null;
        $section_id = $post_data['section_id'] ?? null;
        $status = 0;  // 0 means pending, 1 means approved

        $stmt_student = $conn->prepare("
            INSERT INTO students (user_id, full_name, email, lrn, birth_date, rfid_uid, age, address, sex, guardian, contact, grade_level_id, section_id, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt_student) {
            die("Prepare failed for inserting student: " . $conn->error);
        }

        $stmt_student->bind_param('issssissssiiii', $user_id, $full_name, $email, $lrn, $birth_date, $rfid_uid, $age, $address, $sex, $guardian, $contact, $grade_level_id, $section_id, $status);
        $stmt_student->execute();
        $stmt_student->close();
    } else {
        die('Execute failed: ' . $stmt->error);
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <!-- Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/register_style.css">
    <!-- Google Sign-In Script -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <meta name="google-signin-client_id" content="479928332833-j3qqdcpdo8h3v4p88ah4pdd3kvctc5ra.apps.googleusercontent.com">

    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-image: url('../assets/imgs/bg.png');
            background-size: cover;
            background-position: center;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .g-signin2 {
            margin-top: 10px;
            display: flex;
            justify-content: center;
        }

        .error-message {
            color: red;
            margin-bottom: 15px;
        }

        .g_id_signin {
            width: 100%;
            display: flex;
            justify-content: center;
        }
        .image-container {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    padding-left: 2rem;
}
.modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgb(0,0,0);
    background-color: rgba(0,0,0,0.4);
}

.modal-content {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 400px;
    text-align: center;
    border-radius: 10px;
}

.modal-content .icon {
    font-size: 50px;
    color: green;
}

.modal-content h2 {
    color: green;
    margin-top: 10px;
}

.modal-content p {
    margin: 15px 0;
}

.modal-content .btn {
    background-color: green;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.modal-content .btn:hover {
    background-color: darkgreen;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}
    </style>
</head>
<body>
    <div class="register-container">
        <div class="form-container">
            <img src="../assets/imgs/logo.png" alt="SvM Logo">
            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            <form action="register.php" method="post">
                <div class="form-group">
                    <input type="text" class="form-control" name="full_name" placeholder="Enter Full Name" required>
                    <label for="full_name">Full Name</label>
                </div>
                <div class="form-group">
                    <input type="email" class="form-control" name="email" placeholder="Enter Email" required>
                    <label for="email">Email</label>
                </div>
                <div class="form-group">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter Password" required>
                    <span class="password-toggle" onclick="togglePassword('password')"><i class="bi bi-eye-slash"></i></span>
                    <label for="password">Password</label>
                </div>
                <div class="form-group">
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                    <span class="password-toggle" onclick="togglePassword('confirm_password')"><i class="bi bi-eye-slash"></i></span>
                    <label for="confirm_password">Confirm Password</label>
                </div>
                <div class="form-group">
                    <select class="form-control" name="role" required>
                        <option value="" disabled selected>Select Role</option>
                        <option value="admin">Admin</option>
                        <option value="teacher">Teacher</option>
                        <option value="student">Student</option>
                        <option value="parent">Parent</option>
                    </select>
                    <label for="role">Role</label>
                </div>
                <button type="submit" class="btn btn-primary">Register</button>

               
                <div class="g_id_signin" data-type="standard"></div>

                <div class="login-link">
                    Already have an account? <a href="login.php">Login</a>
                </div>
            </form>
        </div>
        <div class="image-container">
            <img src="../assets/imgs/loginside.png" alt="SvM">
        </div>
    </div>

    <!-- The Modal -->
    <div id="registerModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div class="icon">&#10003;</div>
            <h2>SUCCESS</h2>
            <p>Registration successful!</p>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const passwordToggle = passwordField.nextElementSibling.querySelector('i');
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            passwordToggle.classList.toggle('bi-eye');
            passwordToggle.classList.toggle('bi-eye-slash');
        }

        function showModal() {
            const modal = document.getElementById('registerModal');
            modal.style.display = 'block';
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 3000); // Redirect after 3 seconds
        }

        function closeModal() {
            const modal = document.getElementById('registerModal');
            modal.style.display = 'none';
        }

        <?php if ($registration_success): ?>
            showModal();
        <?php endif; ?>
    </script>
</body>
</html>
