<?php
session_start();
if (!isset($_SESSION['username'])) {
    // Redirect to login if not signed in
    header('Location: admin_login.php');
    exit();
}

// Use the session data to personalize the dashboard
$username = $_SESSION['username'];  // Get username 

// Log out action
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin_login.php');
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap');

        * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

html, body {
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    background-image: url('dark-abstract-blue-light-background-gradient-shapes-navy-blue-hexagon-mesh-pattern-decoration-vector.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
    color: white;
}

@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap');

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

html, body {
    height: 100%;
}

body {
    background-image: url('dark-abstract-blue-light-background-gradient-shapes-navy-blue-hexagon-mesh-pattern-decoration-vector.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
}

header {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    padding: 20px 120px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    z-index: 100;
}

.logo {
    font-size: 22px;
    color: #fff;
    text-decoration: none;
    font-weight: 800;
}

nav a {
    font-size: 22px;
    color: #fff;
    text-decoration: none;
    font-weight: 800;
    margin-left: 35px;
    transition: .3s;
    font-family: 'Times New Roman', Times, serif;
}

nav a:hover,
nav a.active {
    color: rgba(241, 12, 241, 0.8);
}

.auth-buttons {
    display: flex;
    align-items: center;
    gap: 10px;
}

.dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-content {
    display: none;
    position: absolute;
    background-color: #222;
    min-width: 160px;
    box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
    z-index: 1;

}

.dropdown-content a {
    color: white;
    padding: 12px 16px;
    text-decoration: none;
    display: block;
}

.dropdown-content a:hover {
    background-color: #444;
}

.dropdown:hover .dropdown-content {
    display: block;
    opacity: 1;
}

.btn {
    color: white;
    padding: 10px;
    border: none;
    cursor: pointer;
    border-radius: 5px;
}


.signout{
    background-color: #dc3545;
}

.signout:hover {
    background-color: #c82333;
}

@media (max-width: 768px) {
    header {
        padding: 10px 20px;
    }

    nav a {
        font-size: 15px;
        margin-left: 20px;
    }

    .logo {
        font-size: 20px;
    }
}

::-webkit-scrollbar {
    width: 12px;
}

::-webkit-scrollbar-thumb {
    background: #007bff;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
    background: #0056b3;
}


        main {
            flex: 1; /* Allow main to take up remaining space */
            display: flex;
            flex-direction: column; /* Stack content vertically */
            align-items: center; /* Center align items */
            justify-content: center; /* Center vertically */
            text-align: center;
            padding: 30px 50px;
            font-size: 15px;
            line-height: 1.6;
            color: #fff;
            background-color: black;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
            margin-top: 70px; /* Ensure content isn't hidden under fixed header */
        }

        .dashboard-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }

        .dashboard-buttons a {
            padding: 15px 25px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
        }

        .dashboard-buttons a:hover {
            background-color: #0056b3;
        }

        @media (max-width: 768px) {
            header {
                padding: 10px 20px;
            }

            nav a {
                font-size: 15px;
                margin-left: 20px;
            }

            .logo {
                font-size: 20px;
            }

            main {
                padding: 20px 30px;
                font-size: 13px;
            }
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
        <p>Welcome, <?php echo htmlspecialchars($username); ?>!</p>
        <form method="POST" style="display: inline;">
            <button type="submit" name="signout" class="btn signout">Signout</button>
        </form>
    </div>
</header>
<main>
    <h2>Admin Dashboard</h2>
    <p>You can access various details and manage the system from here.</p>

    <div class="dashboard-buttons">
        <a href="student_login_details.php">Student Login Details</a>
        <a href="admin_login_details.php">Admin Login Details</a>
        <a href="student_details_admin.php">Student Details</a>
        <a href="bus_details.php">Bus Details</a>
    </div>
</main>
</body>
</html>
