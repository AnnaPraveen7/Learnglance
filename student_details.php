<?php
session_start();
include 'db_connection.php';

// Check if the student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: student_login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $name = htmlspecialchars($_POST['name']);
    $college = htmlspecialchars($_POST['college']);
    $age = filter_var($_POST['age'], FILTER_VALIDATE_INT);
    $birthdate = $_POST['birthdate'];
    $phone = htmlspecialchars($_POST['phone']);
    $parent_name = htmlspecialchars($_POST['father_name']);
    $parent_phone = htmlspecialchars($_POST['father_phone']);
    $class = htmlspecialchars($_POST['class']);
    $gender = htmlspecialchars($_POST['gender']);
    $country = htmlspecialchars($_POST['country']);
    $student_id = $_SESSION['student_id'];

    // Ensure data is valid
    if ($age === false || strlen($phone) < 10 || strlen($parent_phone) < 10) {
        echo "Please provide valid details.";
        exit();
    }

    // Insert student details into the database
    $query = "INSERT INTO student_details (student_id, name, college, age, birthdate, phone, parent_name, parent_phone, class, gender, country)
              VALUES (:student_id, :name, :college, :age, :birthdate, :phone, :parent_name, :parent_phone, :class, :gender, :country)";

    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'student_id' => $student_id,
        'name' => $name,
        'college' => $college,
        'age' => $age,
        'birthdate' => $birthdate,
        'phone' => $phone,
        'parent_name' => $parent_name,
        'parent_phone' => $parent_phone,
        'class' => $class,
        'gender' => $gender,
        'country' => $country
    ]);

    // Redirect to student dashboard after details are filled
    header('Location: student_dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Details</title>
    <link rel="stylesheet" href="details.css"> 
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
                <a href="student_signin.php">Parent Sign In</a>
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

<div class="container">
    <div class="section-title">Details Form</div>
    <form action="student_details.php" method="POST">
        <div class="form-container">
            <!-- Left Column -->
            <div class="form-column">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="college">College Name</label>
                    <input type="text" id="college" name="college" required>
                </div>
                <div class="form-group">
                    <label for="age">Age</label>
                    <input type="number" id="age" name="age" required>
                </div>
                <div class="form-group">
                    <label for="birthdate">Birthdate</label>
                    <input type="date" id="birthdate" name="birthdate" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>

            <!-- Right Column -->
            <div class="form-column">
                <div class="form-group">
                    <label for="father_name">Father's Name</label>
                    <input type="text" id="father_name" name="father_name" required>
                </div>
                <div class="form-group">
                    <label for="father_phone">Father's Phone Number</label>
                    <input type="tel" id="father_phone" name="father_phone" required>
                </div>
                <div class="form-group">
                    <label for="class">Class</label>
                    <select id="class" name="class" required>
                        <option value="">Select Class</option>
                        <optgroup label="School">
                            <option value="1">Class 1</option>
                            <option value="2">Class 2</option>
                            <option value="3">Class 3</option>
                            <option value="4">Class 4</option>
                            <option value="5">Class 5</option>
                            <option value="6">Class 6</option>
                            <option value="7">Class 7</option>
                            <option value="8">Class 8</option>
                            <option value="2">Class 9</option>
                            <option value="2">Class 10</option>
                            <option value="2">Class 11</option>
                            <option value="2">Class 12</option>
                        </optgroup>
                        <optgroup label="College">
                            <option value="1st_year">1st Year</option>
                            <option value="2nd_year">2nd Year</option>
                            <option value="3rd_year">3rd Year</option>
                            <option value="4th_year">4th Year</option>
                        </optgroup>
                    </select>
                </div>
                <div class="form-group">
                    <label for="country">Country</label>
                    <input type="text" id="country" name="country" required>
                </div>
            </div>
        </div>

        <!-- Submit Button Centered -->
        <div class="form-group form-submit">
            <button type="submit">Submit Details</button>
        </div>
    </form>
</div>


</body>
</html>
