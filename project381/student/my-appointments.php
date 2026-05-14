<?php
require_once '../includes/student-auth.php';
require_once '../includes/db.php';
require_once '../includes/notifications.php';

$studentId = $_SESSION['user_id'];

if (isset($_GET["cancel"])) {
    $appointmentId = (int) $_GET["cancel"];

    $stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = ? AND user_id = ?");
    $stmt->execute([$appointmentId, $studentId]);
    $appointment = $stmt->fetch();

    if ($appointment) {
        $updateStmt = $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ?");
        $updateStmt->execute([$appointmentId]);

        $slotStmt = $pdo->prepare("UPDATE time_slots SET status = 'available' WHERE id = ?");
        $slotStmt->execute([$appointment['time_slot_id']]);

        $adminStmt = $pdo->prepare("SELECT admin_id FROM time_slots WHERE id = ?");
        $adminStmt->execute([$appointment['time_slot_id']]);
        $slot = $adminStmt->fetch();

        if ($slot) {
            createNotification(
                $pdo,
                $slot['admin_id'],
                $_SESSION['name'] . " cancelled an appointment."
            );
        }
    }

    header("Location: my-appointments.php");
    exit();
}

$stmt = $pdo->prepare("SELECT appointments.id, appointments.status, time_slots.date, time_slots.start_time, time_slots.end_time, users.name AS staff_name,
(time_slots.date > CURDATE() OR (time_slots.date = CURDATE() AND time_slots.end_time >= CURTIME())) AS is_upcoming
FROM appointments
JOIN time_slots ON appointments.time_slot_id = time_slots.id
JOIN users ON time_slots.admin_id = users.id
WHERE appointments.user_id = ?
ORDER BY time_slots.date, time_slots.start_time");
$stmt->execute([$studentId]);
$appointments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments | YIC Appointment System</title>
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
                <a href="dashboard.php">Dashboard</a>
                <a href="book-appointment.php">Book Appointment</a>
                <a class="is-active" href="my-appointments.php">My Appointments</a>
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
                <section class="panel-card">
                    <div class="section-heading">
                        <div>
                            <span class="eyebrow">My Appointments</span>
                            <h2>View and manage your bookings</h2>
                            <p>Switch between upcoming, pending, past, and cancelled appointments using the tabs below.</p>
                        </div>
                    </div>

                    <div class="tab-links" role="tablist" aria-label="Appointment tabs">
                        <button class="tab-button is-active" type="button" data-tab-target="upcoming-panel">Upcoming</button>
                        <button class="tab-button" type="button" data-tab-target="pending-panel">Pending</button>
                        <button class="tab-button" type="button" data-tab-target="past-panel">Past</button>
                        <button class="tab-button" type="button" data-tab-target="cancelled-panel">Cancelled</button>
                    </div>

                    <section id="upcoming-panel" class="tab-panel is-active">
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Time</th>
                                        <th>Staff</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $hasUpcoming = false; ?>
                                    <?php foreach ($appointments as $appointment) { ?>
                                        <?php
                                        if (
                                            $appointment['status'] == 'confirmed' &&
                                            $appointment['is_upcoming']) {
                                                $hasUpcoming = true;
                                                ?>
                                            <tr>
                                                <td>#<?php echo htmlspecialchars((string) $appointment['id']); ?></td>
                                                <td><?php echo htmlspecialchars(date("h:i A", strtotime($appointment['start_time']))); ?> - <?php echo htmlspecialchars(date("h:i A", strtotime($appointment['end_time']))); ?></td>
                                                <td><?php echo htmlspecialchars($appointment['staff_name']); ?></td>
                                                <td><?php echo htmlspecialchars($appointment['date']); ?></td>
                                                <td><span class="badge badge-success">Confirmed</span></td>
                                                <td><a class="btn btn-danger btn-small" href="?cancel=<?php echo htmlspecialchars((string) $appointment['id']); ?>">Cancel</a></td>
                                            </tr>
                                        <?php } ?>
                                    <?php } ?>
                                    <?php if (!$hasUpcoming): ?>
                                        <tr>
                                            <td colspan="6" style="text-align:center;">
                                                 No upcoming appointments
                                                </td>
                                            </tr>
                                            <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                    <section id="pending-panel" class="tab-panel">
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Time</th>
                                        <th>Staff</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $hasPending = false; ?>
                                    <?php foreach ($appointments as $appointment) { ?>
                                    <?php
                                    if ($appointment['status'] == 'pending') {
                                        $hasPending = true;
                                        ?>
                                        <tr>
                                            <td>
                                                #<?php echo htmlspecialchars((string) $appointment['id']); ?>
                                            </td>
                                             <td>
                                                <?php echo htmlspecialchars(date("h:i A", strtotime($appointment['start_time']))); ?>
                                                -
                                                <?php echo htmlspecialchars(date("h:i A", strtotime($appointment['end_time']))); ?>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($appointment['staff_name']); ?>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($appointment['date']); ?>

                                            </td>

            
                                            <td>
                
                                            <span class="badge badge-warning">
                    
                                            Pending
                
                                        </span>
            
                                    </td>

            
                                    <td>
                
                    
                                    <a
                    
                                    class="btn btn-danger btn-small"
                    
                                    href="?cancel=<?php echo htmlspecialchars((string) $appointment['id']); ?>"
                
                                    >
                    
                                    Cancel
                
                                </a>
            
                            </td>

        
                        </tr>

    
                        <?php } ?>


                        <?php } ?>


                        <?php if (!$hasPending): ?>


                            <tr>

    
                            <td colspan="6" style="text-align:center;">
        
                            No pending appointments
    
                        </td>


                    </tr>


                    <?php endif; ?>


                </tbody>
                            </table>
                        </div>
                    </section>

                    <section id="past-panel" class="tab-panel">
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Time</th>
                                        <th>Staff</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $hasPast = false; ?>
                                    <?php foreach ($appointments as $appointment) { ?>
                                       <?php
                                      if (
                                        $appointment['status'] == 'confirmed' &&
                                        !$appointment['is_upcoming']) {
                                            $hasPast = true;?>
                                            <tr>
                                                <td>#<?php echo htmlspecialchars((string) $appointment['id']); ?></td>
                                                <td><?php echo htmlspecialchars(date("h:i A", strtotime($appointment['start_time']))); ?> - <?php echo htmlspecialchars(date("h:i A", strtotime($appointment['end_time']))); ?></td>
                                                <td><?php echo htmlspecialchars($appointment['staff_name']); ?></td>
                                                <td><?php echo htmlspecialchars($appointment['date']); ?></td>
                                                <td><span class="badge">Completed</span></td>
                                            </tr>
                                        <?php } ?>
                                    <?php } ?>
                                    <?php if (!$hasPast): ?>
                                        <tr>
                                            <td colspan="5" style="text-align:center;">
        
                                            No past appointments
    
                                        </td>

                                    </tr>

                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section id="cancelled-panel" class="tab-panel">
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Time</th>
                                        <th>Staff</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php $hasCancelled = false; ?>
                                    <?php foreach ($appointments as $appointment) { ?>
                                       <?php
                                       if ($appointment['status'] == 'cancelled') {
                                        
                                        $hasCancelled = true; ?>
                                            <tr>
                                                <td>#<?php echo htmlspecialchars((string) $appointment['id']); ?></td>
                                                <td><?php echo htmlspecialchars(date("h:i A", strtotime($appointment['start_time']))); ?> - <?php echo htmlspecialchars(date("h:i A", strtotime($appointment['end_time']))); ?></td>
                                                <td><?php echo htmlspecialchars($appointment['staff_name']); ?></td>
                                                <td><?php echo htmlspecialchars($appointment['date']); ?></td>
                                                <td><span class="badge badge-danger">Cancelled</span></td>
                                            </tr>
                                        <?php } ?>
                                    <?php } ?>
                                    <?php if (!$hasCancelled): ?>

                                        <tr>
    
                                        <td colspan="5" style="text-align:center;">
        
                                        No cancelled appointments
    
                                    </td>

                                </tr>

                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </section>
            </main>

            <footer class="site-footer">
                <p>&copy; 2026 Yanbu Industrial College.</p>
            </footer>
        </div>
    </div>

    <script src="../assets/JS/script.js"></script>
</body>
</html>