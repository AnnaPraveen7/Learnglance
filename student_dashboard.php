<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: student_login.php');
    exit();
}

$username = $_SESSION['username'];
$user_id = $_SESSION['student_id'];

include 'db_connection.php';

$query = "SELECT name, class FROM student_details WHERE student_id = :student_id";
$stmt = $pdo->prepare($query);
$stmt->execute(['student_id' => $user_id]);
$student_details = $stmt->fetch();

if ($student_details) {
    $student_name = $student_details['name'];
    $student_class = $student_details['class'];
} else {
    $student_name = 'Unknown Student';
    $student_class = 'N/A';
}

$query = "SELECT name FROM student_details WHERE class = :student_class AND student_id != :student_id";
$stmt = $pdo->prepare($query);
$stmt->execute(['student_class' => $student_class, 'student_id' => $user_id]);
$other_students = $stmt->fetchAll();

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: student_login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
<header>
    <a href="#" class="logo">BLUESHIPIN</a>
    <nav>
        <a href="index.php" class="active">Home</a>
        <a href="#about">About</a>
        <a href="#service">Service</a>
        <a href="#help">Help</a>
    </nav>
    <div class="auth-buttons">
        <span>Welcome, <?php echo htmlspecialchars($username); ?></span>
        <a href="index.php?logout=true" class="btn sign-out">Sign Out</a>
    </div>
</header>

<main class="container">
    <h2 class="section-title">Welcome to Dashboard, <?php echo htmlspecialchars($student_name); ?>!</h2>
    <p class="class-info">Class: <?php echo htmlspecialchars($student_class); ?></p>
    
    <div class="center-buttons">
        <a href="create_presentation.php" class="btn">Create </a>
        <a href="view_presentation.php" class="btn">View </a>
    </div>

    <div class="students-list">
        <h3>Other Students in Your Class:</h3>
        <ul>
            <?php if (!empty($other_students)): ?>
                <?php foreach ($other_students as $student): ?>
                    <li><?php echo htmlspecialchars($student['name']); ?></li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>No other students found in your class.</li>
            <?php endif; ?>
        </ul>
    </div>
</main>
</body>
</html>
