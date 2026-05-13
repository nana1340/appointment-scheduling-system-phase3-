<?php
include "../includes/db.php";
include "../includes/admin-auth.php";
include "../includes/notifications.php";


// Confirm appointment
if (isset($_GET['confirm'])) {

    $id = $_GET['confirm'];

    $studentStmt = $pdo->prepare("
        SELECT user_id
        FROM appointments
        WHERE id=?
    ");

    $studentStmt->execute([$id]);
    $appointment = $studentStmt->fetch();

    $pdo->prepare("
        UPDATE appointments 
        SET status='confirmed' 
        WHERE id=?
    ")->execute([$id]);

    if ($appointment) {
        createNotification(
            $pdo,
            $appointment['user_id'],
            "Your appointment has been confirmed."
        );
    }

    header("Location: manage-appointments.php");
    exit();
}


// Cancel appointment
if (isset($_GET['cancel'])) {

    $id = $_GET['cancel'];

    $slotStmt = $pdo->prepare("
        SELECT user_id, time_slot_id
        FROM appointments
        WHERE id=?
    ");

    $slotStmt->execute([$id]);
    $slot = $slotStmt->fetch();

    $pdo->prepare("
        UPDATE appointments 
        SET status='cancelled' 
        WHERE id=?
    ")->execute([$id]);

    if ($slot) {
        $pdo->prepare("
            UPDATE time_slots
            SET status='available'
            WHERE id=?
        ")->execute([$slot['time_slot_id']]);

        createNotification(
            $pdo,
            $slot['user_id'],
            "Your appointment has been cancelled."
        );
    }

    header("Location: manage-appointments.php");
    exit();
}


// Get all appointments with student + slot
$stmt = $pdo->query("
    SELECT 
        a.id,
        u.name,
        u.student_id,
        t.date,
        t.start_time,
        t.end_time,
        a.status
    FROM appointments a
    JOIN users u 
        ON a.user_id = u.id
    JOIN time_slots t 
        ON a.time_slot_id = t.id
    WHERE u.role = 'student'
    ORDER BY t.date ASC, t.start_time ASC
");

$appointments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Manage Appointments | YIC Appointment System
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

                <a class="is-active" href="manage-appointments.php">
                    Manage Appointments
                </a>

                <a href="admin-profile.php">
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

                <section class="panel-card">

                    <span class="eyebrow">
                        Manage Appointments
                    </span>

                    <h2>
                        Review Student Bookings
                    </h2>

                    <div 
                        class="tab-links"
                        role="tablist"
                        aria-label="Admin appointment tabs"
                    >

                        <button
                            class="tab-button is-active"
                            type="button"
                            data-tab-target="upcoming-panel"
                        >
                            Upcoming
                        </button>

                        <button
                            class="tab-button"
                            type="button"
                            data-tab-target="pending-panel"
                        >
                            Pending
                        </button>

                        <button
                            class="tab-button"
                            type="button"
                            data-tab-target="past-panel"
                        >
                            Past
                        </button>

                        <button
                            class="tab-button"
                            type="button"
                            data-tab-target="cancelled-panel"
                        >
                            Cancelled
                        </button>

                    </div>


                    <!-- Upcoming -->
                    <section id="upcoming-panel" class="tab-panel is-active">

                        <div class="table-wrapper">

                            <table>

                                <thead>

                                    <tr>
                                        <th>Student ID</th>
                                        <th>Student</th>
                                        <th>Time</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>

                                </thead>

                                <tbody>

                                    <?php 
                                    $hasUpcoming = false;

                                    foreach ($appointments as $a):

                                        if (
                                            $a['status'] == 'confirmed' &&
                                            strtotime($a['date'].' '.$a['start_time']) >= time()
                                        ):

                                            $hasUpcoming = true;
                                    ?>

                                    <tr>

                                        <td>
                                            <?= htmlspecialchars($a['student_id']) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($a['name']) ?>
                                        </td>

                                        <td>
                                            <?= date("h:i A", strtotime($a['start_time'])) ?>
                                            -
                                            <?= date("h:i A", strtotime($a['end_time'])) ?>
                                        </td>

                                        <td>
                                            <?= date("M d, Y", strtotime($a['date'])) ?>
                                        </td>

                                        <td>

                                            <span class="badge badge-success">
                                                <?= htmlspecialchars(ucfirst($a['status'])) ?>
                                            </span>

                                        </td>

                                        <td>

                                            <a 
                                                href="?cancel=<?= $a['id'] ?>"
                                                class="btn btn-danger btn-small"
                                            >
                                                Cancel
                                            </a>

                                        </td>

                                    </tr>

                                    <?php 
                                        endif;
                                    endforeach;

                                    if (!$hasUpcoming):
                                    ?>

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


                    <!-- Pending -->
                    <section id="pending-panel" class="tab-panel">

                        <div class="table-wrapper">

                            <table>

                                <thead>

                                    <tr>
                                        <th>Student ID</th>
                                        <th>Student</th>
                                        <th>Time</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>

                                </thead>

                                <tbody>

                                    <?php $hasPending = false; ?>

                                    <?php foreach ($appointments as $a): ?>

                                        <?php 
                                        if ($a['status'] == 'pending'):

                                            $hasPending = true;
                                        ?>

                                        <tr>

                                            <td>
                                                <?= htmlspecialchars($a['student_id']) ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($a['name']) ?>
                                            </td>

                                            <td>
                                                <?= date("h:i A", strtotime($a['start_time'])) ?>
                                                -
                                                <?= date("h:i A", strtotime($a['end_time'])) ?>
                                            </td>

                                            <td>
                                                <?= date("M d, Y", strtotime($a['date'])) ?>
                                            </td>

                                            <td>

                                                <span class="badge badge-warning">
                                                    <?= htmlspecialchars(ucfirst($a['status'])) ?>
                                                </span>

                                            </td>

                                            <td>

                                                <form method="GET" style="display:inline;">

                                                    <input
                                                        type="hidden"
                                                        name="confirm"
                                                        value="<?= $a['id'] ?>"
                                                    >

                                                    <button class="btn btn-small">
                                                        Confirm
                                                    </button>

                                                </form>

                                                <form method="GET" style="display:inline;">

                                                    <input
                                                        type="hidden"
                                                        name="cancel"
                                                        value="<?= $a['id'] ?>"
                                                    >

                                                    <button class="btn btn-danger btn-small">
                                                        Cancel
                                                    </button>

                                                </form>

                                            </td>

                                        </tr>

                                        <?php endif; ?>

                                    <?php endforeach; ?>

                                    <?php if (!$hasPending): ?>

                                    <tr>

                                        <td colspan="6" style="text-align:center;">
                                            No pending requests
                                        </td>

                                    </tr>

                                    <?php endif; ?>

                                </tbody>

                            </table>

                        </div>

                    </section>


                    <!-- Past -->
                    <section id="past-panel" class="tab-panel">

                        <div class="table-wrapper">

                            <table>

                                <thead>

                                    <tr>
                                        <th>Student ID</th>
                                        <th>Student</th>
                                        <th>Time</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>

                                </thead>

                                <tbody>

                                    <?php $hasPast = false; ?>

                                    <?php foreach ($appointments as $a): ?>

                                        <?php 
                                        if (
                                            strtotime($a['date'].' '.$a['end_time']) < time() &&
                                            $a['status'] == 'confirmed'
                                        ):

                                            $hasPast = true;
                                        ?>

                                        <tr>

                                            <td>
                                                <?= htmlspecialchars($a['student_id']) ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($a['name']) ?>
                                            </td>

                                            <td>
                                                <?= date("h:i A", strtotime($a['start_time'])) ?>
                                                -
                                                <?= date("h:i A", strtotime($a['end_time'])) ?>
                                            </td>

                                            <td>
                                                <?= date("M d, Y", strtotime($a['date'])) ?>
                                            </td>

                                            <td>

                                                <span class="badge badge-success">
                                                    Completed
                                                </span>

                                            </td>

                                        </tr>

                                        <?php endif; ?>

                                    <?php endforeach; ?>

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


                    <!-- Cancelled -->
                    <section id="cancelled-panel" class="tab-panel">

                        <div class="table-wrapper">

                            <table>

                                <thead>

                                    <tr>
                                        <th>Student ID</th>
                                        <th>Student</th>
                                        <th>Time</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>

                                </thead>

                                <tbody>

                                    <?php $hasCancelled = false; ?>

                                    <?php foreach ($appointments as $a): ?>

                                        <?php 
                                        if ($a['status'] == 'cancelled'):

                                            $hasCancelled = true;
                                        ?>

                                        <tr>

                                            <td>
                                                <?= htmlspecialchars($a['student_id']) ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($a['name']) ?>
                                            </td>

                                            <td>
                                                <?= date("h:i A", strtotime($a['start_time'])) ?>
                                                -
                                                <?= date("h:i A", strtotime($a['end_time'])) ?>
                                            </td>

                                            <td>
                                                <?= date("M d, Y", strtotime($a['date'])) ?>
                                            </td>

                                            <td>

                                                <span class="badge badge-danger">
                                                    <?= htmlspecialchars(ucfirst($a['status'])) ?>
                                                </span>

                                            </td>

                                        </tr>

                                        <?php endif; ?>

                                    <?php endforeach; ?>

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

                <p>
                    &copy; 2026 Yanbu Industrial College.
                </p>

            </footer>

        </div>

    </div>

    <script src="../assets/js/script.js"></script>

</body>

</html>