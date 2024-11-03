<?php
function emailTemplate($reset_link) {
    return "
    <html>
    <head>
        <title>Password Reset</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f4;
                margin: 0;
                padding: 0;
            }
            .container {
                background-color: #ffffff;
                margin: 50px auto;
                padding: 20px;
                max-width: 600px;
                border-radius: 5px;
                box-shadow: 0px 0px 10px 0px rgba(0,0,0,0.1);
            }
            h1 {
                color: #333333;
            }
            p {
                font-size: 16px;
                line-height: 1.6;
            }
            a {
                display: inline-block;
                padding: 10px 20px;
                background-color: #28a745;
                color: white;
                text-decoration: none;
                border-radius: 5px;
            }
                background-color: #218838;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>Password Reset Request</h1>
            <p>Dear User,</p>

            <p>We received a request to reset your password for your School RFID Attendance account. To reset your password, click the button below:</p>

            <p><a href='$reset_link'>Reset Password</a></p>

            <p>If you didn’t request a password reset, please ignore this email. Your account remains secure.</p>

            <p>For any questions or further assistance, feel free to contact our support team.</p>

            <p>Thank you,<br>
            S.V. Montessori, Imus Campus<br>
            <a href='https://www.svm.edu.com/'>https://www.svm.edu.com/</a><br>
            (046) 477-0816 | (0917) 775-4413</p>

            <p>Note: This email is intended for the recipient only. If you received this email by mistake, please contact us immediately.</p>
        </div>
    </body>
    </html>
    ";
}

?>
