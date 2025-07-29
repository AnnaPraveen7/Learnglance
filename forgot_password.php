<?php
session_start();
include 'db_connection.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer files
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Check if email exists in the students table
    $query = "SELECT * FROM students WHERE email = :email";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(50)); // Generate a secure token
        $expires = date("Y-m-d H:i:s", strtotime('+1 hour')); // Token valid for 1 hour

        // Store reset token in the database
        $query = "INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires)";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['email' => $email, 'token' => $token, 'expires' => $expires]);

        // Send Reset Email using PHPMailer
        $reset_link = "http://localhost/test/reset_password.php?token=$token";

        $mail = new PHPMailer(true);

        try {
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'alenbiju787@gmail.com'; // Your Gmail
            $mail->Password = 'annl ugxi momz fxkn'; // Use App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Sender & Recipient
            $mail->setFrom('alenbiju787@gmail.com', 'Alen biju');
            $mail->addAddress($email);

            // Email Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body = "Click <a href='$reset_link'>here</a> to reset your password. <br> This link is valid for 1 hour.";

            $mail->send();
            $_SESSION['success_message'] = "A password reset link has been sent to your email.";
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Error sending email: {$mail->ErrorInfo}";
        }
    } else {
        $_SESSION['error_message'] = "Email not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
        }
        form {
            display: inline-block;
            background: #f4f4f4;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        input[type="email"] {
            padding: 10px;
            width: 250px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            background: #28a745;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background: #218838;
        }
    </style>
</head>
<body>

    <h2>Forgot Password?</h2>
    <p>Enter your email address to receive a password reset link.</p>

    <?php if (isset($_SESSION['error_message'])): ?>
        <p style="color: red;"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
    <?php endif; ?>
    <?php if (isset($_SESSION['success_message'])): ?>
        <p style="color: green;"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></p>
    <?php endif; ?>

    <form action="forgot_password.php" method="POST">
        <input type="email" name="email" required placeholder="Enter your email">
        <br>
        <button type="submit">Send Reset Link</button>
    </form>

</body>
</html>
