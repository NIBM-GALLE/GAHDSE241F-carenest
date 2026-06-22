<?php
session_start();
// If already logged in, redirect to appropriate dashboard
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/dashboard.php");
    } elseif ($_SESSION['role'] == 'staff') {
        header("Location: staff/dashboard.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>carenest Daycare</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <i class="fas fa-child"></i>
                <span>carenest Daycare</span>
            </div>
            <ul class="nav-menu">
                <li><a href="#home">Home</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#services">Services</a></li>
                <li><a href="#contact">Contact</a></li>
                <li><a href="login.php" class="btn-login-nav">Login</a></li>
            </ul>
        </div>
    </nav>

    <section id="home" class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Welcome to <span class="highlight">carenest</span> Daycare</h1>
                <p>Where every child discovers joy in learning, grows with confidence, and builds friendships that last a lifetime.</p>
                <a href="login.php" class="btn btn-primary">Login to Portal</a>
            </div>
        </div>
    </section>

    <section id="about" class="about">
        <div class="container">
            <h2 class="section-title">About Our Daycare</h2>
            <div class="about-grid">
                <div class="about-card">
                    <i class="fas fa-heart"></i>
                    <h3>Loving Environment</h3>
                    <p>Safe, nurturing space where children feel valued.</p>
                </div>
                <div class="about-card">
                    <i class="fas fa-graduation-cap"></i>
                    <h3>Quality Education</h3>
                    <p>Age-appropriate curriculum for development.</p>
                </div>
                <div class="about-card">
                    <i class="fas fa-shield-alt"></i>
                    <h3>Safety First</h3>
                    <p>Secure facility with trained staff.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2024 carenest Daycare. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>