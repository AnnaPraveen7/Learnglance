<?php
session_start();
include 'db_connection.php'; // Include your database connection

// Check if the admin is logged in
if (!isset($_SESSION['username'])) {
    header('Location: admin_login.php');
    exit();
}

// Fetch student details
$query = "SELECT * FROM student_details";
$stmt = $pdo->prepare($query);
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle student addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $student_id = $_POST['student_id'];
    $name = $_POST['name'];
    $college = $_POST['college'];
    $age = $_POST['age'];
    $birthdate = $_POST['birthdate'];
    $phone = $_POST['phone'];
    $parent_name = $_POST['parent_name'];
    $parent_phone = $_POST['parent_phone'];
    $class = $_POST['class'];
    $gender = $_POST['gender'];
    $country = $_POST['country'];

    $insert_query = "INSERT INTO student_details (student_id, name, college, age, birthdate, phone, parent_name, parent_phone, class, gender, country)
                     VALUES (:student_id, :name, :college, :age, :birthdate, :phone, :parent_name, :parent_phone, :class, :gender, :country)";
    $insert_stmt = $pdo->prepare($insert_query);
    $insert_stmt->execute([
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

    header("Location: student_details.php");
    exit();
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_confirm'])) {
    try {
        $student_id = $_POST['student_id'];
        $delete_query = "DELETE FROM student_details WHERE student_id = :student_id";
        $delete_stmt = $pdo->prepare($delete_query);
        $delete_stmt->execute(['student_id' => $student_id]);

        header("Location: student_details.php");
        exit();
    } catch (Exception $e) {
        echo "Error deleting student: " . $e->getMessage();
    }
}
?>

<!-- HTML CONTENT REMAINS SAME WITH REPLACED LABELS -->

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Same head and style as before -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Details</title>
    <!-- Keep the same styles here -->
    <!-- ... -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-image: url('bus-that-is-lit-up-night-city_850140-88.avif');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding-top: 60px; /* Prevent content overlap with header */
        }

        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            padding: 20px 120px;
            background: rgba(17, 20, 26, 0.9);
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .logo {
            font-size: 25px;
            color: #fff;
            text-decoration: none;
            font-weight: 600;
        }

        nav a {
            font-size: 17px;
            color: #fff;
            text-decoration: none;
            font-weight: 300;
            margin-left: 35px;
            transition: .3s;
        }

        nav a:hover,
        nav a.active {
            color: rgb(23, 202, 202);
        }

        .welcome-signout {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn {
            background-color: #007bff;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .signout-btn {
            background-color: #dc3545;
        }

        .signout-btn:hover {
            background-color: #c82333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 18px;
            text-align: left;
            overflow-y: auto;
            max-height: 400px;
            display: block;
        }

        table th, table td {
            padding: 15px;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #007bff;
            color: white;
        }

        .delete-btn {
            background-color: red;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .delete-btn:hover {
            background-color: darkred;
        }

        form {
            margin-top: 20px;
            padding: 10px;
        }

        /* Scrollable section */
        tbody {
            display: block;
            max-height: 300px;
            overflow-y: scroll;
        }

        thead, tbody tr {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .main {
            max-width: 1300px;
            margin: 100px auto; /* Adjusted to prevent overlap with fixed header */
            text-align: center;
            padding: 30px 50px;
            background-color: rgba(0, 0, 0, 0.7);
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
        }

        h2, h3 {
            margin-bottom: 20px;
            font-weight: 600;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        input {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            margin-bottom: 10px;
        }

        input:focus {
            outline: none;
            box-shadow: 0 0 5px rgba(23, 202, 202, 0.5);
        }

        .back-btn, .add-btn {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
        }

        .back-btn:hover, .add-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<header>
    <a href="#" class="logo">BLUESHIPIN</a>
    <nav>
        <a href="index.php" class="active">Home</a>
        <a href="#about">About</a>
        <a href="#service">Service</a>
        <a href="#help">Help</a>
        <a href="#cancel">Cancellation</a>
    </nav>
    <div class="welcome-signout">
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
        <form method="POST" action="admin_dashboard.php" style="display: inline;">
            <button type="submit" name="signout" class="btn signout-btn">Sign Out</button>
        </form>
    </div>
</header>

<div class="main">
    <h2>Student Details</h2>

    <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>College</th>
                <th>Age</th>
                <th>Birthdate</th>
                <th>Phone</th>
                <th>Parent Name</th>
                <th>Parent Phone</th>
                <th>Class</th>
                <th>Gender</th>
                <th>Country</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($students as $student): ?>
            <tr>
                <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                <td><?php echo htmlspecialchars($student['name']); ?></td>
                <td><?php echo htmlspecialchars($student['college']); ?></td>
                <td><?php echo htmlspecialchars($student['age']); ?></td>
                <td><?php echo htmlspecialchars($student['birthdate']); ?></td>
                <td><?php echo htmlspecialchars($student['phone']); ?></td>
                <td><?php echo htmlspecialchars($student['parent_name']); ?></td>
                <td><?php echo htmlspecialchars($student['parent_phone']); ?></td>
                <td><?php echo htmlspecialchars($student['class']); ?></td>
                <td><?php echo htmlspecialchars($student['gender']); ?></td>
                <td><?php echo htmlspecialchars($student['country']); ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($student['student_id']); ?>">
                        <button type="submit" name="delete_confirm" class="delete-btn">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Add New Student</h3>
    <form method="POST">
        <input type="number" name="student_id" placeholder="Student ID" required>
        <input type="text" name="name" placeholder="Name" required>
        <input type="text" name="college" placeholder="College" required>
        <input type="number" name="age" placeholder="Age" required>
        <input type="date" name="birthdate" placeholder="Birthdate" required>
        <input type="text" name="phone" placeholder="Phone" required>
        <input type="text" name="parent_name" placeholder="Parent Name" required>
        <input type="text" name="parent_phone" placeholder="Parent Phone" required>
        <input type="text" name="class" placeholder="Class" required>
        <select name="gender" required>
            <option value="">Select Gender</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>
        </select>
        <input type="text" name="country" placeholder="Country" required>
        <button type="submit" name="add_student" class="btn add-btn">Add Student</button>
    </form>

    <a href="admin_dashboard.php" class="back-btn">Back to Dashboard</a>
</div>
</body>
</html>
