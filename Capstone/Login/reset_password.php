<?php
include '../db.php';
include 'functions.php';

$error_message = '';
$success_message = '';
$token = $_GET['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['token'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($new_password !== $confirm_password) {
        $error_message = 'Passwords do not match!';
    } else {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update the user's password in the database
        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ? AND reset_token_expiry > NOW()");
        $stmt->bind_param("ss", $hashed_password, $token);

        if ($stmt->execute()) {
            $success_message = 'Your password has been successfully reset.';
        } else {
            $error_message = 'Invalid or expired token.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="css/login_style.css">
</head>

<body>
    <div class="login-container">
        <div class="form-container">
            <img src="../assets/imgs/logo.png" alt="SvM Logo">
            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <?php echo $error_message; ?>
                </div>
            <?php elseif (!empty($success_message)): ?>
                <div class="success-message">
                    <?php echo $success_message; ?>
                </div>
            <?php else: ?>
                <form action="reset_password.php" method="post">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    <div class="form-group">
                        <input type="password" class="form-control" name="new_password" placeholder="Enter New Password"
                            required>
                        <label for="new_password">New Password</label>
                    </div>
                    <div class="form-group">
                        <input type="password" class="form-control" name="confirm_password"
                            placeholder="Confirm New Password" required>
                        <label for="confirm_password">Confirm Password</label>
                    </div>
                    <button type="submit" class="btn btn-primary">Reset Password</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>