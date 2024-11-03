<?php
require '../db.php'; // Ensure the correct path to your db.php file is included
require '../PHPMailer-master/src/PHPMailer.php';
require '../PHPMailer-master/src/SMTP.php';
require '../PHPMailer-master/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';

    // Check if the email exists in the database
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    if ($stmt === false) {
        $error_message = 'Prepare failed: ' . $conn->error;
    } else {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Email exists, proceed with password reset
            $user = $result->fetch_assoc();
            $reset_token = bin2hex(random_bytes(16));
            $reset_link = "http://localhost/Capstone/Login/update_password.php?token=$reset_token"; // Replace //https://svmrfidsystem.com/Login/update_password.php?token=$reset_token//

            // Save the reset token to the database
            $stmt = $conn->prepare("UPDATE users SET reset_token = ? WHERE email = ?");
            $stmt->bind_param("ss", $reset_token, $email);
            $stmt->execute();

            // Send reset link via email using PHPMailer
            $mail = new PHPMailer(true);

            try {
                //Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'jocatayoc15@gmail.com'; // Your email
                $mail->Password = 'clib pybu yztl bavo'; // Your generated app password
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                //Recipients
                $mail->setFrom('jocatayoc15@gmail.com', 'RFIDSchool'); // Adjust 'RFIDSchool' to the name you want
                $mail->addAddress($email);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                $mail->Body = "
                    <html>
                    <body>
                    <p>Dear User,</p>
                    <p>You requested a password reset for your School RFID Attendance account. Please use the link below to reset your password:</p>
                    <p><a href='$reset_link'>Reset Password</a></p>
                    <p>If you didn’t request a password reset, please disregard this email. Your account remains secure.</p>
                    <p>Thank you!</p>
                    <p>Best regards,</p>
                    <p>S.V. Montessori, Imus Campus<br>
                    Contact: (046) 477-0816 | (0917) 775-4413<br>
                    <a href='https://www.svm.edu.com/'>https://www.svm.edu.com/</a></p>
                    </body>
                    </html>";
                $mail->AltBody = "Click this link to reset your password: $reset_link";

                $mail->send();
                $success_message = 'If your email is correct, you will receive a reset link shortly.';
            } catch (Exception $e) {
                $error_message = 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo;
            }
        } else {
            $error_message = 'Email does not exist.';
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <!-- Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/login_style.css">
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

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.6); /* Slightly darker background */
            backdrop-filter: blur(8px); /* Apply blur to background */
        }

        .modal-content {
            background-color: #fff;
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

        .alert-message {
            background-color: #dff0d8;
            color: #3c763d;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #d6e9c6;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
        }

        .error-message {
            color: red;
            margin-bottom: 15px;
        }

        .back-button-container {
            text-align: center;
            margin-top: 20px;
        }

        .back-button {
            display: inline-block;
            color: #fff;
            background-color: #006400;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .back-button:hover {
            background-color: #004d00;
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
            <?php if (!empty($success_message)): ?>
                <div class="alert-message">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            <form action="forgot_password.php" method="post">
                <div class="form-group">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email" required>
                    <label for="email">Email</label>
                </div>
                <button type="submit" class="btn btn-primary">Request Password Reset</button>
                <div class="back-button-container">
                    <a href="login.php" class="back-button">← Back to Login</a>
                </div>
            </form>
        </div>
        <div class="image-container">
            <img src="../assets/imgs/loginside.png" alt="SvM"> <!-- Change to your logo image path -->
        </div>
    </div>

    <!-- The Modal -->
    <div id="successModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div class="icon">&#10003;</div>
            <h2>SUCCESS</h2>
            <p>If your email is correct, you will receive a reset link shortly.</p>
            
        </div>
    </div>

    <script>
        function showModal() {
            const modal = document.getElementById('successModal');
            modal.style.display = 'block';
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 3000); // Redirect after 3 seconds
        }

        function closeModal() {
            const modal = document.getElementById('successModal');
            modal.style.display = 'none';
        }

        <?php if (!empty($success_message)): ?>
            showModal();
        <?php endif; ?>
    </script>
</body>
</html>
