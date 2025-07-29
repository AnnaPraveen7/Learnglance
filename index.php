<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interactive Learning Website</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Miniver&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php session_start(); ?>
    <!-- Header Section -->
    <header>
        <a href="#" class="logo">BLUESHIPIN</a>
        <nav>
            <a href="#home" class="active">Home</a>
            <a href="#about">About</a>
            <a href="#services">Services</a>
            <a href="#contact">Contact</a>
        </nav>
        <div class="auth-buttons">
                    <div class="dropdown">
                        <button class="btn sign-in">Sign In</button>
                        <div class="dropdown-content">
                            <a href="student_signin.php">Student Sign In</a>
                            <a href="admin_signin.php">Admin Sign In</a>
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

    <!-- Home Section -->
    <section id="home" class="content">
        <h1>LEARNGLANCE</h1>
        <p class="short-note">Your one-stop platform for interactive and personalized learning experiences.</p>
        <button class="register-bus" onclick="window.location.href='student_login.php'">Start Learning</button>

    </section>

    <!-- About Section -->
    <section id="about" class="about-content">
        <h2>About Us</h2>
        <p>LearnHub is designed to make learning accessible and engaging for everyone. From personalized content to interactive modules, we ensure a seamless learning experience for all age groups.</p>
    </section>

    <!-- Services Section -->
    <section id="services" class="service-content">
        <h2>Our Services</h2>
        <p>We offer a wide range of services including:</p>
        <ul>
            <li>Interactive lessons</li>
            <li>Personalized progress tracking</li>
            <li>Live expert sessions</li>
            <li>Extensive course library</li>
        </ul>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="cancel-content">
        <h2>Contact Us</h2>
        <p>Have questions or need help? Reach out to us at <a href="mailto:support@learnhub.com">support@learnhub.com</a> or call us at +123-456-7890.</p>
        <button class="cancel-now">Get in Touch</button>
    </section>

    <!-- Footer Section -->
    <footer>
        <div class="footer-content">
            <h3>Contact Us</h3>
            <p>Get in touch with us for any queries or support.</p>
            <ul class="contact-info">
                <li><strong>Email:</strong> <i class="fas fa-envelope"></i> support@blueshipin.com</li>
                <li><strong>Phone:</strong> <i class="fas fa-phone"></i> +91 9876543210</li>
                <li><strong>WhatsApp:</strong> <i class="fab fa-whatsapp"></i> +91 9876543210</li>
                <li><strong>Instagram:</strong> <i class="fab fa-instagram"></i> <a href="https://instagram.com/blueshipin" target="_blank">@blueshipin</a></li>
                <li><strong>Facebook:</strong> <i class="fab fa-facebook"></i> <a href="https://facebook.com/blueshipin" target="_blank">@blueshipin</a></li>
                <li><strong>Address:</strong> <i class="fas fa-map-marker-alt"></i> 123 Blueshipin Road, New Delhi, India</li>
                <li><strong>Working Hours:</strong> <i class="fas fa-clock"></i> Monday to Friday, 9 AM to 6 PM IST</li>
            </ul>
    
            <div class="about-section">
                <h4>About Us</h4>
                <p>We are committed to providing excellent service and support to our customers. Our mission is to...</p>
            </div>
    
            <div class="links">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="/privacy-policy">Privacy Policy</a></li>
                    <li><a href="/terms-of-service">Terms of Service</a></li>
                    <li><a href="/faq">FAQ</a></li>
                </ul>
            </div>
    
            
        </div>
    </footer>
    
</body>
</html>
