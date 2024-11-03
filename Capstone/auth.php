<?php
session_start();

function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

function login($username, $password)
{
    global $conn;
    
    // Fetch user by username
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verify the hashed password
        if (password_verify($password, $user['password'])) {
            // Set session variable
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username']; // Optional
            $_SESSION['role'] = $user['role']; // Optional
            
            return true;
        }
    }
    return false;
}

function logout()
{
    session_destroy();
    unset($_SESSION['user_id']);
    header("Location: ../Login/login.php"); // Redirect to login page after logout
    exit();
}
?>
