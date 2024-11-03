<?php
session_start(); // Start the session

include '../db.php';
include 'functions.php';

$error_message = '';
$login_success = false;
$redirect_url = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    if ($conn->connect_error) {
        $error_message = 'Database connection error: ' . $conn->connect_error;
    } else {
        // Query to check if the user exists and is not archived
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = ? AND archived = 0");
        $stmt->bind_param("ss", $email, $role);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user['password'])) {
                $login_success = true;

                // Store user information in session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                // Set the redirect URL based on role
                switch ($role) {
                    case 'teacher':
                        $redirect_url = "../Teacher/dashboard.php";
                        break;
                    case 'admin':
                        $redirect_url = "../Admin/index.php";
                        break;
                    case 'student':
                        $redirect_url = "../Student/dashboard.php";
                        break;
                    case 'parent':
                        $redirect_url = "../Parent/dashboard.php";
                        break;
                    default:
                        $error_message = "Invalid role";
                        break;
                }
            } else {
                $error_message = 'Invalid email or password.';
            }
        } else {
            $error_message = 'User not found or archived.';
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css"
        rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/login_style.css">
    <!-- Google Sign-In Script -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <meta name="google-signin-client_id"
        content="479928332833-j3qqdcpdo8h3v4p88ah4pdd3kvctc5ra.apps.googleusercontent.com">

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

        /* Additional styles */
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
    margin-bottom: 10px; /* Adjust spacing as needed */
}

        .modal-header {
            font-size: 20px;
            font-weight: bold;
            color: #00796b;
        }
        
    </style>
</head>

<body>
    <div class="login-container">
        <div class="form-container">
            <img src="../assets/imgs/logo.png" alt="SvM Logo"> <!-- Change to your logo image path -->
            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            <form action="login.php" method="post">
                <div class="form-group">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email" required>
                    <label for="email">Email</label>
                </div>
                <div class="form-group">
                    <input type="password" class="form-control" id="password" name="password"
                        placeholder="Enter Password" required>
                    <label for="password">Password</label>
                    <span class="password-toggle" onclick="togglePassword()"><i class="bi bi-eye-slash"></i></span>
                </div>
                <div class="form-group">
                    <select class="form-control" id="role" name="role" required>
                        <option value="" disabled selected>Select Role</option>
                        <option value="admin">Admin</option>
                        <option value="teacher">Teacher</option>
                        <option value="student">Student</option>
                        <option value="parent">Parent</option>
                    </select>
                    <label for="role">Role</label>
                </div>
                <a href="forgot_password.php" class="forgot-password">Forgot Password?</a>
                <button type="submit" class="btn btn-primary">Login</button>

                

                <div class="g_id_signin" data-type="standard" data-size="large" data-theme="outline"
                    data-text="signin_with" data-shape="rectangular" data-logo_alignment="left"></div>

                <div class="register-link">Don't have an account? <a href="register.php">Register</a></div>
            </form>
        </div>
        <div class="image-container">
            <img src="../assets/imgs/loginside.png" alt="SvM">
        </div>
    </div>
    <!-- Success Modal -->
    <div id="successModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <div class="icon">&#10003;</div> <!-- Icon moved here -->
        <div class="modal-header">Login Successful</div>
        <div class="modal-body">
            <p>Welcome! Redirecting you to your dashboard...</p>
        </div>
    </div>
</div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        <?php if ($login_success): ?>
            $(document).ready(function() {
                $('#successModal').modal('show');
            });
        <?php endif; ?>


        function togglePassword() {
            const passwordField = document.getElementById('password');
            const passwordToggle = document.querySelector('.password-toggle i');
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            passwordToggle.classList.toggle('bi-eye');
            passwordToggle.classList.toggle('bi-eye-slash');
        }



        function showModal() {
            const modal = document.getElementById('successModal');
            modal.style.display = 'block';
            setTimeout(() => {
                window.location.href = "<?php echo $redirect_url; ?>";
            }, 3000); // Redirect after 3 seconds
        }

        function closeModal() {
            const modal = document.getElementById('successModal');
            modal.style.display = 'none';
        }

        <?php if ($login_success): ?>
            showModal();
        <?php endif; ?>
    </script>
</body>

</html>