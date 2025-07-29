<?php
session_start();
include 'db_connection.php'; // Include your database connection

// Check if the admin is logged in
if (!isset($_SESSION['username'])) {
    // Redirect to login page if not logged in
    header('Location: admin_login.php');
    exit();
}

// Fetch parent login details from the database
$query = "SELECT id, username, email FROM students"; // Selecting only the necessary fields
$stmt = $pdo->prepare($query);
$stmt->execute();
$parents = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login Details</title>
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

        .signout-btn {
            background-color: #dc3545;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .signout-btn:hover {
            background-color: #c82333;
        }

        main {
            max-width: 1300px;
            margin: 100px auto; /* Adjusted to prevent overlap with fixed header */
            text-align: center;
            padding: 30px 50px;
            background-color: rgba(0, 0, 0, 0.7);
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
        }

        h2 {
            margin-bottom: 20px;
            font-weight: 600;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 18px;
            text-align: left;
            overflow: hidden;
        }

        table th, table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #007bff;
            color: white;
            position: sticky;
            top: 0; /* Make the header sticky */
            z-index: 10; /* Ensure it stays above other elements */
        }

        tbody {
            display: block;
            max-height: 300px; /* Adjust height for scrolling */
            overflow-y: scroll; /* Enable scrolling */
        }

        thead, tbody tr {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .back-btn {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
        }

        .back-btn:hover {
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
                <button type="submit" name="signout" class="signout-btn">Signout</button>
            </form>
        </div>
    </header>
    <main>
        <h2>Students Login Details</h2>
        
        <!-- Table to display parent login details -->
        <table>
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Username</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($parents as $parent): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($parent['id']); ?></td>
                        <td><?php echo htmlspecialchars($parent['username']); ?></td>
                        <td><?php echo htmlspecialchars($parent['email']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Back button to return to admin dashboard -->
        <a href="admin_dashboard.php" class="back-btn">Back to Dashboard</a>
    </main>
</body>
</html>
