<?php
require_once '../includes/student-auth.php';
require_once '../includes/db.php';
require_once '../includes/notifications.php';

$studentId = $_SESSION['user_id'];
$message = "";
$messageColor = "#0b7a43";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["slot_id"])) {
    $slotId = (int) $_POST["slot_id"];

    $checkStmt = $pdo->prepare("SELECT * FROM time_slots WHERE id = ? AND status = 'available'");
    $checkStmt->execute([$slotId]);
    $slot = $checkStmt->fetch();

    $appointmentCheck = $pdo->prepare("
    SELECT id FROM appointments
    WHERE user_id = ?
    AND time_slot_id = ?
");

$appointmentCheck->execute([$studentId, $slotId]);

if ($slot && !$appointmentCheck->fetch()) {
        $insertStmt = $pdo->prepare("INSERT INTO appointments (user_id, time_slot_id, status, booking_date) VALUES (?, ?, ?, NOW())");
        $insertStmt->execute([$studentId, $slotId, 'pending']);

        $updateStmt = $pdo->prepare("UPDATE time_slots SET status = 'booked' WHERE id = ?");
        $updateStmt->execute([$slotId]);

        createNotification(
            $pdo,
            $slot['admin_id'],
            "New appointment request from " . $_SESSION['name'] . "."
        );

        $message = "Appointment booked successfully.";
    } else {
        $message = "This appointment is already booked or unavailable.";
        $messageColor = "#c62828";
    }
}

$staff = $_GET["staff"] ?? "all";
$date = $_GET["date"] ?? "";

$sql = "SELECT time_slots.*, users.name AS staff_name
FROM time_slots
JOIN users ON time_slots.admin_id = users.id
WHERE time_slots.status = 'available'
AND (
    time_slots.date > CURDATE()
    OR (time_slots.date = CURDATE() AND time_slots.end_time >= CURTIME())
)
";
$params = [];

if ($staff != "all") {
    $sql .= " AND users.name = ?";
    $params[] = $staff;
}

if ($date != "") {
    $sql .= " AND time_slots.date = ?";
    $params[] = $date;
}

$sql .= " ORDER BY time_slots.date, time_slots.start_time";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$slots = $stmt->fetchAll();

$staffStmt = $pdo->query("SELECT name FROM users WHERE role = 'admin'");
$staffList = $staffStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment | YIC Appointment System</title>
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
                <a class="is-active" href="book-appointment.php">Book Appointment</a>
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
                <section class="panel-card">
                    <div class="section-heading">
                        <div>
                            <span class="eyebrow">Book Appointment</span>
                            <h2>Find an available slot</h2>
                            <p>Use the filters to browse appointment options by staff member or date.</p>
                        </div>
                    </div>

                    <form class="filter-grid" method="GET" action="">
                        <div>
                            <label for="staff-filter">Staff Member</label>
                            <select id="staff-filter" name="staff">
                                <option value="all">All Staff</option>
                                <?php foreach ($staffList as $staffMember) { ?>
                                    <option value="<?php echo htmlspecialchars($staffMember['name']); ?>" <?php if ($staff == $staffMember['name']) echo 'selected'; ?>><?php echo htmlspecialchars($staffMember['name']); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div>
                            <label for="date-filter">Date</label>
                            <input id="date-filter" name="date" type="date" value="<?php echo htmlspecialchars($date); ?>">
                        </div>
                        <div class="filter-action">
                            <button class="btn btn-primary" type="submit">Search</button>
                        </div>
                    </form>

                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Staff</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($slots as $slot) { ?>
                                    <tr>
                                        <td>
    
                                        <?php echo htmlspecialchars(date("h:i A", strtotime($slot['start_time']))); ?>
   
                                        -
    
                                        <?php echo htmlspecialchars(date("h:i A", strtotime($slot['end_time']))); ?>

                                    </td>
                                        <td><?php echo htmlspecialchars($slot['staff_name']); ?></td>
                                        <td><?php echo htmlspecialchars($slot['date']); ?></td>
                                        <td><span class="badge badge-success">Available</span></td>
                                        <td>
                                            <form method="POST" action="">
                                                <input type="hidden" name="slot_id" value="<?php echo htmlspecialchars((string) $slot['id']); ?>">
                                                <button class="btn btn-small" type="submit">Book</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php } ?>
                                <?php if (count($slots) == 0) { ?>
                                    <tr>
                                        <td colspan="5" style="text-align:center;">
    
                                        No available slots found.

                                    </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                    <p class="form-message" style="color: <?php echo $messageColor; ?>;"><?php echo htmlspecialchars($message); ?></p>
                </section>
            </main>

            <footer class="site-footer">
                <p>&copy; 2026 Yanbu Industrial College.</p>
            </footer>
        </div>
    </div>
</body>
</html>
