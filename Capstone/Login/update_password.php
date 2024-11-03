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
        
        // Check if the statement was prepared successfully
        if ($stmt === false) {
            // Output the error for diagnosis
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
    <title>Update Password</title>
    <!-- Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css" rel="stylesheet">
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
        
        .update-password-container {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .update-password-container img {
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
            right: -5px;
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

        .back-to-login {
            margin-top: 20px;
            display: inline-block;
            font-size: 16px;
        }

        .back-to-login a {
            color: #28a745;
            text-decoration: none;
        }

        .back-to-login a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="update-password-container">
        <img src="../assets/imgs/logo.png" alt="SvM Logo"> <!-- Same logo as in login/forgot password -->

        <?php if ($error_message): ?>
            <div class="error-message">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="success-message">
                <?php echo $success_message; ?>
                <div class="back-to-login"><a href="login.php">Go to Login</a></div>
            </div>
        <?php else: ?>
            <form action="update_password.php" method="POST">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? '', ENT_QUOTES); ?>">
                <div class="form-group">
                    <input type="password" name="new_password" id="new_password" placeholder="New Password" required>
                    <i class="bi bi-eye eye-toggle" id="toggleNewPassword"></i>
                </div>
                <div class="form-group">
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm New Password" required>
                    <i class="bi bi-eye eye-toggle" id="toggleConfirmPassword"></i>
                </div>
                <button type="submit" class="btn-primary">Update Password</button>
            </form>
        <?php endif; ?>
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
    </script>
</body>
</html>
