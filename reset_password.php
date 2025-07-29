<?php
session_start();
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $_SESSION['error_message'] = "Passwords do not match.";
    } else {
        // Check token validity
        $query = "SELECT * FROM password_resets WHERE token = :token AND expires_at > NOW()";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['token' => $token]);
        $reset_entry = $stmt->fetch();

        if ($reset_entry) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Update student password
            $updateQuery = "UPDATE students SET password = :password WHERE email = :email";
            $updateStmt = $pdo->prepare($updateQuery);
            $updateStmt->execute(['password' => $hashed_password, 'email' => $reset_entry['email']]);

            // Delete used token
            $deleteQuery = "DELETE FROM password_resets WHERE email = :email";
            $deleteStmt = $pdo->prepare($deleteQuery);
            $deleteStmt->execute(['email' => $reset_entry['email']]);

            $_SESSION['success_message'] = "Password has been reset. You can now log in.";
            header("Location: student_login.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Invalid or expired token.";
        }
    }
}

// Retrieve token from URL
$token = $_GET['token'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
</head>
<body>
    <h2>Reset Password</h2>
    <?php if (isset($_SESSION['error_message'])): ?>
        <p style="color: red;"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
    <?php endif; ?>
    <form action="reset_password.php" method="POST">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <label>New Password:</label>
        <input type="password" name="password" required>
        <label>Confirm Password:</label>
        <input type="password" name="confirm_password" required>
        <button type="submit">Reset Password</button>
    </form>
</body>
</html>
