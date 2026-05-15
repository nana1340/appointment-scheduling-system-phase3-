<?php
include "../includes/db.php";
include "../includes/admin-auth.php";
 
// Get current admin data
$stmt = $pdo->prepare("
    SELECT * 
    FROM users 
    WHERE email = ?
");

$stmt->execute([$_SESSION['user']]);

$admin = $stmt->fetch();


// Get next confirmed appointment for this admin only
$stmt2 = $pdo->prepare("
    SELECT 
        u.name,
        t.date,
        t.start_time,
        t.end_time,
        a.status
    FROM appointments a
    JOIN users u 
        ON a.user_id = u.id
    JOIN time_slots t 
        ON a.time_slot_id = t.id
    WHERE a.status = 'confirmed'
    AND t.admin_id = ?
    AND (
        t.date > CURDATE()
        OR (t.date = CURDATE() AND t.end_time >= CURTIME())
    )
    ORDER BY t.date ASC, t.start_time ASC
    LIMIT 1
");

$stmt2->execute([$_SESSION['user_id']]);
$next = $stmt2->fetch();


// Update profile
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    if ($name == "" || $email == "" || $phone == "") {
        echo "<script>alert('Please fill in all fields');</script>";

    } elseif (!str_ends_with($email, "@yic.edu.sa") || str_ends_with($email, "@stu.yic.edu.sa")) {
        echo "<script>alert('Admin email must end with @yic.edu.sa');</script>";

    } else {

        $update = $pdo->prepare("
            UPDATE users 
            SET 
                name = ?, 
                email = ?, 
                phone = ?
            WHERE id = ?
        ");

        $update->execute([
            $name,
            $email,
            $phone,
            $admin['id']
        ]);

        $_SESSION['user'] = $email;
        $_SESSION['name'] = $name;

        $stmt = $pdo->prepare("
            SELECT * 
            FROM users 
            WHERE id = ?
        ");

        $stmt->execute([$admin['id']]);

        $admin = $stmt->fetch();

        echo "<script>alert('Profile updated successfully');</script>";
    }
}


$badge = 'badge-success';

if ($next && $next['status'] == 'pending') {
    $badge = 'badge-warning';
}

if ($next && $next['status'] == 'cancelled') {
    $badge = 'badge-danger';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Admin Profile | YIC Appointment System
    </title>

    <link rel="stylesheet" href="../assets/css/style.css">

</head>

<body>

    <div class="page-shell">

        <aside class="sidebar">

            <div class="brand">

                <div>
                    <h1>Appointment</h1>
                    <p>YIC Appointment</p>
                </div>

            </div>

            <nav class="nav-links">

                <a href="admin-dashboard.php">
                    Dashboard
                </a>

                <a href="manage-slots.php">
                    Manage Slots
                </a>

                <a href="manage-appointments.php">
                    Manage Appointments
                </a>

                <a class="is-active" href="admin-profile.php">
                    Profile
                </a>

            </nav>

        </aside>

        <div class="content-area">

            <header class="topbar">

                <div class="brand">

                    <span class="brand-mark">
                        YIC
                    </span>

                    <div>
                        <h1>Appointment Scheduling System</h1>
                        <p>Yanbu Industrial College</p>
                    </div>

                </div>

            </header>

            <main class="main-content">

                <section class="panel-card profile-layout">

                    <article class="profile-summary">

                        <div class="avatar-circle">
                            <?= htmlspecialchars(strtoupper(substr($admin['name'], 0, 1))) ?>
                        </div>

                        <h2>
                            <?= htmlspecialchars($admin['name']) ?>
                        </h2>

                        <span class="badge">
                            Admin
                        </span>

                        <div class="mini-card">

                            <h3>Next Appointment</h3>

                            <?php if ($next): ?>

                                <p>
                                    Date:
                                    <?= date("F d, Y", strtotime($next['date'])) ?>
                                </p>

                                <p>
                                    Time:
                                    <?= date("h:i A", strtotime($next['start_time'])) ?>
                                    -
                                    <?= date("h:i A", strtotime($next['end_time'])) ?>
                                </p>

                                <p>
                                    Student:
                                    <?= htmlspecialchars($next['name']) ?>
                                </p>

                                <p>
                                    <span class="badge <?= $badge ?>">
                                        <?= htmlspecialchars(ucfirst($next['status'])) ?>
                                    </span>
                                </p>

                            <?php else: ?>

                                <p>
                                    No confirmed appointment
                                </p>

                            <?php endif; ?>

                        </div>

                    </article>

                    <section>

                        <form class="profile-form" method="POST">

                            <h3>
                                Profile Information
                            </h3>

                            <div class="form-grid">

                                <div>

                                    <label for="admin-name">
                                        Full Name
                                    </label>

                                    <input
                                        id="admin-name"
                                        name="full_name"
                                        type="text"
                                        value="<?= htmlspecialchars($admin['name']) ?>"
                                        required
                                    >

                                </div>

                                <div>

                                    <label for="admin-email">
                                        Email Address
                                    </label>

                                    <input
                                        id="admin-email"
                                        name="email"
                                        type="email"
                                        value="<?= htmlspecialchars($admin['email']) ?>"
                                        required
                                    >

                                </div>

                                <div>

                                    <label for="admin-phone">
                                        Phone Number
                                    </label>

                                    <input
                                        id="admin-phone"
                                        name="phone"
                                        type="tel"
                                        value="<?= htmlspecialchars($admin['phone']) ?>"
                                        required
                                    >

                                </div>

                            </div>

                            <div class="button-row">

                                <button type="submit" class="btn btn-primary">
                                    Save Changes
                                </button>

                            </div>

                            <hr>

                            <div class="profile-actions">
                                <a class="btn btn-danger" href="../logout.php">
                                    Logout
                                </a>

                            </div>

                            <p class="form-message" aria-live="polite"></p>

                        </form>

                    </section>

                </section>

            </main>

            <footer class="site-footer">

                <p>
                    &copy; 2026 Yanbu Industrial College.
                </p>

            </footer>

        </div>

    </div>

    <script src="../assets/js/script.js"></script>

</body>

</html>
