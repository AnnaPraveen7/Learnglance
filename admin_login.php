
<?php
session_start();
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Fetch student record
    $query = "SELECT * FROM admins WHERE username = :username";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['student_id'] = $user['id'];  
        $_SESSION['username'] = $user['username'];  
        header('Location: admin_dashboard.php');
        exit();
    } else {
        // Invalid login
        $error_message = "Invalid username or password. Please try again.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="studentlog.css">
</head>
<body>
<header>
    <a href="index.php" class="logo">BLUESHIPIN</a>
    <nav>
        <a href="index.php" class="active">Home</a>
        <a href="#about">About</a>
        <a href="#service">Service</a>
        <a href="#help">Help</a>
    </nav>
    <div class="auth-buttons">
            <div class="dropdown">
                <button class="btn sign-in">Sign In</button>
                <div class="dropdown-content">
                    <a href="student_signin.php">Student Sign In</a>
                </div>
            </div>
            <div class="dropdown">
                <button class="btn login">Login</button>
                <div class="dropdown-content">
                    <a href="student_login.php">Student Login</a>
                    <a href="admin_login.php">Admin Login</a>
                </div>
            </div>
    </div>
</header>
<main>
    <h2>Admin Login</h2>
    <?php if (isset($error_message)): ?>
        <p style="color: red;"><?php echo $error_message; ?></p>
    <?php endif; ?>
    <form action="admin_login.php" method="POST">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        
        <button class="btn login-in-btn">Login</button>
    </form>
    <p>Don't have an account? <a href="admin_signin.php">Sign In</a></p>
</main>
</body>
</html>
