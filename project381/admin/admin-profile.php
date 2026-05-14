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


// Get next appointment
$stmt2 = $pdo->query("
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
    WHERE CONCAT(t.date, ' ', t.start_time) >= NOW()
    AND a.status != 'cancelled'
    ORDER BY t.date ASC, t.start_time ASC
    LIMIT 1
");

$next = $stmt2->fetch();


// Update profile
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name = htmlspecialchars($_POST['full_name']);
    $email = htmlspecialchars($_POST['email']);
    $phone = htmlspecialchars($_POST['phone']);

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

    $stmt = $pdo->prepare("
        SELECT * 
        FROM users 
        WHERE id = ?
    ");

    $stmt->execute([$admin['id']]);

    $admin = $stmt->fetch();

    echo "<script>alert('Profile updated successfully');</script>";
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
                            <?= strtoupper(substr($admin['name'], 0, 1)) ?>
                        </div>

                        <h2>
                            <?= $admin['name'] ?>
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
                                    <?= $next['name'] ?>
                                </p>

                                <p>
                                    <span class="badge <?= $badge ?>">
                                        <?= ucfirst($next['status']) ?>
                                    </span>
                                </p>

                            <?php else: ?>

                                <p>
                                    No upcoming appointment
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
                                        value="<?= $admin['name'] ?>"
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
                                        value="<?= $admin['email'] ?>"
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
                                        value="<?= $admin['phone'] ?>"
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