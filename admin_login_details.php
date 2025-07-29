<?php
session_start();
include 'db_connection.php'; // Include your database connection

// Check if the admin is logged in
if (!isset($_SESSION['username'])) {
    // Redirect to login page if not logged in
    header('Location: admin_login.php');
    exit();
}

// Fetch admin login details from the database
$query = "SELECT id, username, email FROM admins";
$stmt = $pdo->prepare($query);
$stmt->execute();
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle admin addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if username or email already exists
    $check_query = "SELECT * FROM admins WHERE username = :username OR email = :email";
    $check_stmt = $pdo->prepare($check_query);
    $check_stmt->execute(['username' => $username, 'email' => $email]);
    $existing_admin = $check_stmt->fetch();

    if ($existing_admin) {
        $error_message = "Username or email already exists.";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new admin into the database
        $insert_query = "INSERT INTO admins (username, email, password) VALUES (:username, :email, :password)";
        $insert_stmt = $pdo->prepare($insert_query);
        $insert_stmt->execute([
            'username' => $username,
            'email' => $email,
            'password' => $hashed_password
        ]);

        // Redirect after successful addition
        header("Location: admin_login_details.php");
        exit();
    }
}

// Handle admin deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_confirm'])) {
    $admin_id = $_POST['admin_id'];
    $entered_username = $_POST['confirm_username'];

    // Fetch the username based on admin_id
    $fetch_query = "SELECT username FROM admins WHERE id = :id";
    $fetch_stmt = $pdo->prepare($fetch_query);
    $fetch_stmt->execute(['id' => $admin_id]);
    $admin = $fetch_stmt->fetch();

    // Check if the entered username matches the admin username
    if ($admin && $entered_username === $admin['username']) {
        $delete_query = "DELETE FROM admins WHERE id = :id";
        $delete_stmt = $pdo->prepare($delete_query);
        $delete_stmt->execute(['id' => $admin_id]);
        header("Location: admin_login_details.php");
        exit();
    } else {
        $error_message = "The username does not match. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login Details</title>
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

        .error-message {
            color: red;
            font-weight: bold;
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
    </style>
</head>
<body>
    <header>
        <div class="logo">Admin Dashboard</div>
        <nav>
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="admin_login_details.php" class="active">Admin Details</a>
            <a href="logout.php" class="signout-btn">Sign Out</a>
        </nav>
    </header>

    <div class="main">
        <h2>Admin Login Details</h2>

        <?php if (isset($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admins as $admin): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($admin['id']); ?></td>
                        <td><?php echo htmlspecialchars($admin['username']); ?></td>
                        <td><?php echo htmlspecialchars($admin['email']); ?></td>
                        <td>
                            <form action="" method="post">
                                <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                                <input type="text" name="confirm_username" placeholder="Confirm Username" required>
                                <button type="submit" name="delete_confirm" class="delete-btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="container">
            <h2>Add New Admin</h2>
            <form action="" method="post">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" name="add_admin" class="btn">Add Admin</button>
            </form>
        </div>

        <a href="admin_dashboard.php" class="back-btn">Back to Dashboard</a>
    </div>
</body>
</html>
