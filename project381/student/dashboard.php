<?php
require_once '../includes/student-auth.php';
require_once '../includes/db.php';
require_once '../includes/notifications.php';

$studentId = $_SESSION['user_id'];
$studentName = $_SESSION['name'];

if (isset($_GET['read_notification'])) {
    markNotificationAsRead($pdo, (int) $_GET['read_notification'], $studentId);
    header("Location: dashboard.php");
    exit();
}

$stmt = $pdo->prepare("SELECT time_slots.date, time_slots.start_time, time_slots.end_time, users.name AS staff_name, appointments.status
FROM appointments
JOIN time_slots ON appointments.time_slot_id = time_slots.id
JOIN users ON time_slots.admin_id = users.id
WHERE appointments.user_id = ?
AND appointments.status = 'confirmed'
AND (
    time_slots.date > CURDATE()
    OR (time_slots.date = CURDATE() AND time_slots.end_time >= CURTIME())
)
ORDER BY time_slots.date, time_slots.start_time
LIMIT 1");
$stmt->execute([$studentId]);
$nextAppointment = $stmt->fetch();

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM appointments
JOIN time_slots ON appointments.time_slot_id = time_slots.id
WHERE appointments.user_id = ?
AND appointments.status = 'confirmed'
AND (
    time_slots.date > CURDATE()
    OR (time_slots.date = CURDATE() AND time_slots.end_time >= CURTIME())
)");
$countStmt->execute([$studentId]);
$upcomingCount = $countStmt->fetchColumn();

$notifications = getUnreadNotifications($pdo, $studentId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | YIC Appointment System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="page-shell">
        <aside class="sidebar">
            <div class="brand brand-sidebar">
                <div>
                    <h1>Appointment</h1>
                    <p>YIC Appointment</p>
                </div>
            </div>
            <nav class="nav-links">
                <a class="is-active" href="dashboard.php">Dashboard</a>
                <a href="book-appointment.php">Book Appointment</a>
                <a href="my-appointments.php">My Appointments</a>
                <a href="profile.php">My Profile</a>
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
                    <div>
                        <span class="eyebrow">Dashboard</span>
                        <h2>Welcome back, <?php echo htmlspecialchars($studentName); ?></h2>
                        <p>Manage your appointments, check your next booking, and move quickly between the most important pages.</p>
                    </div>
                </section>

                <section class="card-grid two-columns">
                    <article class="info-card">
                        <h3>Next Appointment</h3>
                        <?php if ($nextAppointment) { ?>
                            <p><strong>Date:</strong> <?php echo htmlspecialchars($nextAppointment['date']); ?></p>
                            <p>
    <strong>Time:</strong>
    <?php echo htmlspecialchars(date("h:i A", strtotime($nextAppointment['start_time']))); ?>
    -
    <?php echo htmlspecialchars(date("h:i A", strtotime($nextAppointment['end_time']))); ?>
</p>
                            <p><strong>Staff:</strong> <?php echo htmlspecialchars($nextAppointment['staff_name']); ?></p>
                            <p><strong>Status:</strong> <span class="badge <?php echo $nextAppointment['status'] == 'pending' ? 'badge-warning' : 'badge-success'; ?>"><?php echo htmlspecialchars(ucfirst($nextAppointment['status'])); ?></span></p>
                        <?php } else { ?>
                            <p>No upcoming appointment.</p>
                        <?php } ?>
                    </article>

                    <article class="info-card accent-card">
                        <h3>Upcoming Appointments</h3>
                        <p class="metric"><?php echo htmlspecialchars((string) $upcomingCount); ?></p>
                        <p>You currently have active bookings in your schedule.</p>
                    </article>
                </section>
                <section class="card-grid two-columns">

    <article class="info-card">

        <h3>Quick Actions</h3>

        <p>
            <a href="book-appointment.php">
                Book a new appointment
            </a>
        </p>

        <p>
            <a href="my-appointments.php">
                View my appointments
            </a>
        </p>

        <p>
            <a href="profile.php">
                Update my profile
            </a>
        </p>

    </article>

</section>

                <section class="panel-card">
                    <h3>Notifications</h3>

                    <?php if (count($notifications) > 0) { ?>
                        <?php foreach ($notifications as $notification) { ?>
                            <div class="notification-item">
                                <p><?php echo htmlspecialchars($notification['message']); ?></p>
                                <a class="btn btn-small" href="?read_notification=<?php echo htmlspecialchars((string) $notification['id']); ?>">Mark as read</a>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <p>No new notifications.</p>
                    <?php } ?>
                </section>
            </main>

            <footer class="site-footer">
                <p>&copy; 2026 Yanbu Industrial College.</p>
            </footer>
        </div>
    </div>
</body>
</html>