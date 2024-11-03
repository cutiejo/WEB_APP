<?php
header('Content-Type: application/json');
require '../db.php'; // Ensure correct path to db.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer-master/src/PHPMailer.php';
require '../PHPMailer-master/src/SMTP.php';
require '../PHPMailer-master/src/Exception.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $email = $data['email'] ?? '';

    if (!empty($email)) {
        // Check if the email exists in the database
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        if ($stmt === false) {
            $response['message'] = 'Database query failed';
            echo json_encode($response);
            exit;
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Email exists, generate reset token
            $reset_token = bin2hex(random_bytes(16));
            $reset_link = "http://192.168.1.12/Capstone/api/redirect.php?token=$reset_token"; // Use deep link for mobile app

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
                $mail->setFrom('jocatayoc15@gmail.com', 'RFIDAttendanceSchool');
                $mail->addAddress($email);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                $mail->Body = "
                    <html>
                    <body>
                    <p>Dear Student,</p>
                    <p>You requested a password reset for your School RFID Attendance account. Please use the link below to reset your password:</p>
                    <p><a href='$reset_link'>Reset Password</a></p>
                    <p>If you didn’t request a password reset, please disregard this email. Your account remains secure.</p>
                    <p>Thank you!</p>
                    <p>Best regards,<br>
                    S.V. Montessori, Imus Campus<br>
                    Contact: (046) 477-0816 | (0917) 775-4413<br>
                    <a href='https://www.svm.edu.com/'>https://www.svm.edu.com/</a></p>
                    </body>
                    </html>";
                $mail->AltBody = "Click this link to reset your password: $reset_link";

                $mail->send();
                $response['message'] = 'If your email is correct, you will receive a reset link shortly.';
            } catch (Exception $e) {
                $response['message'] = 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo;
            }
        } else {
            $response['message'] = 'Email does not exist.';
        }
        $stmt->close();
    } else {
        $response['message'] = 'Email is required.';
    }
    $conn->close();
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
