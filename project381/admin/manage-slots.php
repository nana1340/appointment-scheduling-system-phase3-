<?php
include "../includes/db.php";
include "../includes/admin-auth.php";


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $date = trim($_POST['date']);
    $start_time = trim($_POST['start_time']);
    $end_time = trim($_POST['end_time']); 


    if ($date == "" || $start_time == "" || $end_time == "") {

        $error = "Please fill in all fields";

    } elseif ($end_time <= $start_time) {

        $error = "End time must be after start time";

    } else {

        $stmt = $pdo->prepare("
            INSERT INTO time_slots 
            (
                date,
                start_time,
                end_time,
                status,
                admin_id
            )
            VALUES (?, ?, ?, 'available', ?)
        ");

        $stmt->execute([
            $date,
            $start_time,
            $end_time,
            $_SESSION['user_id']
        ]);

        header("Location: manage-slots.php");
        exit();
    }
}


// Delete slot
if (isset($_GET['delete'])) {

    $id = (int) $_GET['delete'];

    $slotCheck = $pdo->prepare("
        SELECT id
        FROM time_slots
        WHERE id = ?
        AND admin_id = ?
    ");

    $slotCheck->execute([$id, $_SESSION['user_id']]);
    $slot = $slotCheck->fetch();

    if (!$slot) {

        $error = "Slot not found";

    } else {

        $check = $pdo->prepare("
            SELECT COUNT(*)
            FROM appointments
            WHERE time_slot_id = ?
            AND status != 'cancelled'
        ");

        $check->execute([$id]);

        if ($check->fetchColumn() > 0) {

            $error = "This slot has an appointment and cannot be deleted";

        } else {

            $pdo->prepare("
                DELETE FROM appointments
                WHERE time_slot_id = ?
                AND status = 'cancelled'
            ")->execute([$id]);

            $pdo->prepare("
                DELETE FROM time_slots 
                WHERE id = ?
                AND admin_id = ?
            ")->execute([$id, $_SESSION['user_id']]);

            header("Location: manage-slots.php");
            exit();
        }
    }
}


// Get current and future slots for the logged-in admin only
$stmt = $pdo->prepare("
    SELECT * 
    FROM time_slots 
    WHERE admin_id = ?
    AND (
        date > CURDATE()
        OR (date = CURDATE() AND end_time >= CURTIME())
    )
    ORDER BY date ASC, start_time ASC
");

$stmt->execute([$_SESSION['user_id']]);
$slots = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Manage Slots | YIC Appointment System
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

                <a class="is-active" href="manage-slots.php">
                    Manage Slots
                </a>

                <a href="manage-appointments.php">
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
                        Manage Slots
                    </span>

                    <h2>
                        Add New Time Slot
                    </h2>


                    <form method="POST" class="filter-grid">

                        <div>

                            <label for="slot-date">
                                Date
                            </label>

                            <input
                                id="slot-date"
                                name="date"
                                type="date"
                                required
                            >

                        </div>


                        <div>

                            <label for="start-time">
                                Start Time
                            </label>

                            <input
                                id="start-time"
                                name="start_time"
                                type="time"
                                required
                            >

                        </div>


                        <div>

                            <label for="end-time">
                                End Time
                            </label>

                            <input
                                id="end-time"
                                name="end_time"
                                type="time"
                                required
                            >

                        </div>


                        <div class="filter-action">

                            <button class="btn btn-primary" type="submit">
                                Add Slot
                            </button>

                        </div>

                    </form>


                    <?php if(isset($error)): ?>

                        <p class="form-message">
                            <?= htmlspecialchars($error) ?>
                        </p>

                    <?php endif; ?>


                    <p class="form-message" aria-live="polite"></p>


                    <div class="table-wrapper">

                        <table>

                            <thead>

                                <tr>
                                    <th>Time</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php if (empty($slots)): ?>

                                <tr>

                                    <td colspan="4" style="text-align:center;">
                                        No slots available
                                    </td>

                                </tr>

                                <?php endif; ?>


                                <?php foreach ($slots as $s): ?>

                                <tr>

                                    <td>

                                        <?= date("h:i A", strtotime($s['start_time'])) ?>
                                        -
                                        <?= date("h:i A", strtotime($s['end_time'])) ?>

                                    </td>

                                    <td>

                                        <?= date("M d, Y", strtotime($s['date'])) ?>

                                    </td>

                                    <td>

                                        <span class="
                                            badge
                                            <?= $s['status'] == 'booked'
                                                ? 'badge-warning'
                                                : 'badge-success'
                                            ?>
                                        ">

                                            <?= htmlspecialchars(ucfirst($s['status'])) ?>

                                        </span>

                                    </td>

                                    <td>

                                        <a
                                            href="?delete=<?= htmlspecialchars((string) $s['id']) ?>"
                                            class="btn btn-danger btn-small"
                                        >
                                            Delete
                                        </a>

                                    </td>

                                </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

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
