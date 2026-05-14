<?php
session_start();
include "includes/db.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("
        SELECT * FROM users
        WHERE email = ?
    ");

    $stmt->execute([$email]);

    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {

        $_SESSION['user'] = $user['email'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['user_id'] = $user['id'];

        if ($user['role'] == 'admin') {
            header("Location: admin/admin-dashboard.php");
        } else {
            header("Location: student/dashboard.php");
        }

        exit();

    } else {
        $error = "Invalid email or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | YIC Appointment System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
    <header class="topbar simple-topbar">
        <div class="brand">
            <span class="brand-mark">YIC</span>
            <div>
                <h1>Appointment Scheduling System</h1>
                <p>Yanbu Industrial College</p>
            </div>
        </div>
    </header>

    <main class="main-content">
        <section class="auth-card">
            <article class="auth-visual">
                <span class="eyebrow">Student Login</span>
                <h2>Welcome back!</h2>
                <p>Sign in to browse available slots, manage your appointments, and review your profile.</p>
            </article>

            <section class="auth-form-panel">
                <form class="auth-form" method="POST">
                    <h3>Log In</h3>
                    <label for="login-email">University Email</label>
                    <input id="login-email" name="email" type="email" placeholder="student@stu.yic.edu.sa" required>

                    <label for="login-password">Password</label>
                    <input id="login-password" name="password" type="password" placeholder="Enter your password" minlength="8" required>

                    <button type="submit" class="btn btn-primary btn-block">Log In</button>
                    <?php if(isset($error)): ?>
    <p class="form-message"><?= $error ?></p>
<?php endif; ?>
                    <p class="muted-text">Don't have an account? <a href="register.php">Sign up</a></p>
                    <p class="form-message" aria-live="polite"></p>
                </form>
            </section>
        </section>
    </main>

    <footer class="site-footer">
        <p>© 2026 Yanbu Industrial College.</p>
    </footer>

    <script src="assets/js/script.js"></script>
</body>
</html>