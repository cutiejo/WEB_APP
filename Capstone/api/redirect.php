<?php
require '../db.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $reset_token = $_POST['reset_token'] ?? '';

    if ($new_password !== $confirm_password) {
        $error_message = 'Passwords do not match!';
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Prepare the SQL statement
        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL WHERE reset_token = ?");
        if ($stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($conn->error));
        }

        // Bind parameters and execute the statement
        $stmt->bind_param("ss", $hashed_password, $reset_token);
        if ($stmt->execute()) {
            $success_message = 'Password has been successfully updated!';
        } else {
            $error_message = 'Execute failed: ' . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    }
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <!-- Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 120vh;
            background-image: url('../assets/imgs/bg.png');
            background-size: cover;
            background-position: center;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        
        .reset-password-container {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 40px;
            align-items: center;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .reset-password-container img {
            width: 100px;
            margin-bottom: 20px;
        }

        .form-group {
            position: relative;
            margin-bottom: 15px;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        .eye-toggle {
            position: absolute;
            right: 10px;
            top: 10px;
            cursor: pointer;
        }

        .btn-primary {
            background-color: #28a745;
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-primary:hover {
            background-color: #218838;
        }

        .success-message, .error-message {
            color: green;
            font-size: 18px;
            margin-bottom: 15px;
        }

        .error-message {
            color: red;
        }

        .back-to-app {
            margin-top: 20px;
            display: inline-block;
            font-size: 16px;
        }

        .back-to-app a {
            color: #28a745;
            text-decoration: none;
        }

        .back-to-app a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="reset-password-container">
        <img src="../assets/imgs/logo.png" alt="SvM Logo"> <!-- Same logo as in login/forgot password -->

        <div class="form-group">
            <input type="password" name="new_password" id="new_password" placeholder="New Password" required>
            <i class="bi bi-eye eye-toggle" id="toggleNewPassword"></i>
        </div>
        <div class="form-group">
            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
            <i class="bi bi-eye eye-toggle" id="toggleConfirmPassword"></i>
        </div>
        <button class="btn-primary" onclick="resetPassword()">Reset Password</button>
        <div>
            <p class="back-to-app">Once your password is updated, <a href="your-app://login">return to the app to log in</a>.</p>
        </div>
        
    </div>

    <script>
        const toggleNewPassword = document.querySelector("#toggleNewPassword");
        const newPassword = document.querySelector("#new_password");
        const toggleConfirmPassword = document.querySelector("#toggleConfirmPassword");
        const confirmPassword = document.querySelector("#confirm_password");

        toggleNewPassword.addEventListener("click", function() {
            const type = newPassword.getAttribute("type") === "password" ? "text" : "password";
            newPassword.setAttribute("type", type);
            this.classList.toggle("bi-eye-slash");
        });

        toggleConfirmPassword.addEventListener("click", function() {
            const type = confirmPassword.getAttribute("type") === "password" ? "text" : "password";
            confirmPassword.setAttribute("type", type);
            this.classList.toggle("bi-eye-slash");
        });

        function resetPassword() {
            // Implement the password reset functionality here
        }
    </script>
</body>
</html>