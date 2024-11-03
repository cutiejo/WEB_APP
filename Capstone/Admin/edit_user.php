<?php
include 'db.php';
include 'auth.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: users.php');
    exit;
}

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username'], $_POST['password'], $_POST['role'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $stmt = $conn->prepare("UPDATE users SET username = ?, password = ?, role = ? WHERE id = ?");
    $stmt->bind_param("sssi", $username, $password, $role, $id);
    $stmt->execute();
    header('Location: users.php');
    exit;
}

$user = $conn->query("SELECT * FROM users WHERE id = $id")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <h1>Edit User</h1>
    <form method="POST" action="">
        <input type="text" name="username" value="<?php echo $user['username']; ?>" required>
        <input type="password" name="password" value="<?php echo $user['password']; ?>" required>
        <select name="role" required>
            <option value="admin" <?php if ($user['role'] == 'admin')
                echo 'selected'; ?>>Admin</option>
            <option value="user" <?php if ($user['role'] == 'user')
                echo 'selected'; ?>>User</option>
        </select>
        <button type="submit">Update User</button>
    </form>
</body>

</html>