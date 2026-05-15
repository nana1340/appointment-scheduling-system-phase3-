<?php
include "../includes/db.php";
include "../includes/admin-auth.php";
include "../includes/notifications.php";

$adminId = $_SESSION['user_id'];

if (isset($_GET['read_notification'])) {
    markNotificationAsRead($pdo, (int) $_GET['read_notification'], $adminId);
    header("Location: admin-dashboard.php");
    exit();
}

$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM time_slots
    WHERE admin_id = ?
    AND (
        date > CURDATE()
        OR (date = CURDATE() AND end_time >= CURTIME())
    )
");
$stmt->execute([$adminId]);
$totalSlots = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM time_slots 
    WHERE status = 'booked'
    AND admin_id = ?
    AND (
        date > CURDATE()
        OR (date = CURDATE() AND end_time >= CURTIME())
    )
");
$stmt->execute([$adminId]);
$bookedSlots = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM appointments a
    JOIN time_slots t
        ON a.time_slot_id = t.id
    WHERE a.status = 'pending'
    AND t.admin_id = ?
    AND (
        t.date > CURDATE()
        OR (t.date = CURDATE() AND t.end_time >= CURTIME())
    )
");
$stmt->execute([$adminId]);
$pending = $stmt->fetchColumn();

$totalStudents = $pdo->query("
    SELECT COUNT(*) 
    FROM users 
    WHERE role = 'student'
")->fetchColumn();

$stmt = $pdo->prepare(" 
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

$stmt->execute([$adminId]);
$next = $stmt->fetch();

$badge = 'badge-success';

if ($next && $next['status'] == 'pending') {
    $badge = 'badge-warning';
}

if ($next && $next['status'] == 'cancelled') {
    $badge = 'badge-danger';
}

$notifications = getUnreadNotifications($pdo, $adminId);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | YIC Appointment System</title>

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
                <a class="is-active" href="admin-dashboard.php">Dashboard</a>
                <a href="manage-slots.php">Manage Slots</a>
                <a href="manage-appointments.php">Manage Appointments</a>
                <a href="admin-profile.php">Profile</a>
            </nav>

        </aside>

        <div class="content-area">

            <header class="topbar">

                <div class="brand">
                    <span class="brand-mark">YIC</span>

                    <div>
                        <h1>Appointment Scheduling System</h1>
                        <p>Yanbu Industrial College</p>
                    </div>
                </div>

            </header>

            <main class="main-content">

                <section class="hero-card">

                    <span class="eyebrow">Dashboard</span>

                    <h2>
                        Welcome back,
                        <?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?>
                    </h2>

                    <p>
                        Manage available slots, review bookings,
                        and monitor appointment activity.
                    </p>

                </section>

                <section class="card-grid four-columns">

                    <article class="info-card">
                        <h3>Total Slots</h3>
                        <p class="metric"><?= $totalSlots ?></p>
                    </article>

                    <article class="info-card">
                        <h3>Booked Slots</h3>
                        <p class="metric"><?= $bookedSlots ?></p>
                    </article>

                    <article class="info-card accent-card">
                        <h3>Pending Requests</h3>
                        <p class="metric"><?= $pending ?></p>
                    </article>

                    <article class="info-card">
                        <h3>Total Students</h3>
                        <p class="metric"><?= $totalStudents ?></p>
                    </article>

                </section>

                <section class="card-grid two-columns">

                    <article class="info-card">

                        <h3>Next Appointment</h3>

                        <?php if ($next): ?>

                            <p>
                                <strong>Student:</strong>
                                <?= htmlspecialchars($next['name']) ?>
                            </p>

                            <p>
                                <strong>Date:</strong>
                                <?= htmlspecialchars($next['date']) ?>
                            </p>

                            <p>
                                <strong>Time:</strong>
                                <?= htmlspecialchars($next['start_time']) ?>
                                -
                                <?= htmlspecialchars($next['end_time']) ?>
                            </p>

                            <p>
                                <strong>Status:</strong>

                                <span class="badge <?= $badge ?>">
                                    <?= htmlspecialchars($next['status']) ?>
                                </span>
                            </p>

                        <?php else: ?>

                            <p>No confirmed appointments yet</p>

                        <?php endif; ?>

                    </article>

                    <article class="info-card">

                        <h3>Quick Actions</h3>

                        <p>
                            <a href="manage-slots.php">
                                Add or delete time slots
                            </a>
                        </p>

                        <p>
                            <a href="manage-appointments.php">
                                Review student bookings
                            </a>
                        </p>

                        <p>
                            <a href="admin-profile.php">
                                Update admin profile
                            </a>
                        </p>

                    </article>

                </section>

                <section class="panel-card">
                    <h3>Notifications</h3>

                    <?php if (count($notifications) > 0): ?>
                        <?php foreach ($notifications as $notification): ?>
                            <div class="notification-item">
                                <p><?= htmlspecialchars($notification['message']) ?></p>
                                <a class="btn btn-small" href="?read_notification=<?= htmlspecialchars((string) $notification['id']) ?>">
                                    Mark as read
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No new notifications.</p>
                    <?php endif; ?>
                </section>

            </main>

            <footer class="site-footer">
                <p>&copy; 2026 Yanbu Industrial College.</p>
            </footer>

        </div>
    </div>

    <script src="../assets/js/script.js"></script>

</body>
</html>
