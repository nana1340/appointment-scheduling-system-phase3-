<?php
include "../includes/db.php";
include "../includes/admin-auth.php";


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];


    if ($end_time <= $start_time) {

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

    $id = $_GET['delete'];

    $check = $pdo->prepare("
        SELECT COUNT(*)
        FROM appointments
        WHERE time_slot_id=?
    ");

    $check->execute([$id]);

    if ($check->fetchColumn() > 0) {

        $error = "This slot has an appointment and cannot be deleted";

    } else {

        $pdo->prepare("
            DELETE FROM time_slots 
            WHERE id=?
        ")->execute([$id]);

        header("Location: manage-slots.php");
        exit();
    }
}


// Get all slots
$stmt = $pdo->query("
    SELECT * 
    FROM time_slots 
    ORDER BY date ASC
");

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
                            <?= $error ?>
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

                                            <?= ucfirst($s['status']) ?>

                                        </span>

                                    </td>

                                    <td>

                                        <a
                                            href="?delete=<?= $s['id'] ?>"
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