<?php
require_once '../includes/student-auth.php';
require_once '../includes/db.php';

$studentId = $_SESSION['user_id'];
$message = "";
$messageColor = "#0b7a43";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $studentNumber = trim($_POST["student_id"] ?? "");
    $major = trim($_POST["major"] ?? "");
    $year = trim($_POST["year_level"] ?? "");
    $phone = trim($_POST["phone"] ?? "");

    if ($name == "" || $email == "" || $studentNumber == "" || $major == "" || $year == "" || $phone == "") {
        $message = "Please fill in all required fields.";
        $messageColor = "#c62828";
    } elseif (strpos($email, "yic.edu.sa") === false) {
        $message = "Please enter a valid YIC email.";
        $messageColor = "#c62828";
    } else {
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $checkStmt->execute([$email, $studentId]);

        if ($checkStmt->fetch()) {
            $message = "Email already registered.";
            $messageColor = "#c62828";
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, student_id = ?, major = ?, year = ?, phone = ? WHERE id = ?");
            $stmt->execute([$name, $email, $studentNumber, $major, $year, $phone, $studentId]);

            $_SESSION["name"] = $name;
            $_SESSION["email"] = $email;

            $message = "Profile updated successfully.";
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$studentId]);
$student = $stmt->fetch();

$nextStmt = $pdo->prepare("SELECT time_slots.date, time_slots.start_time, time_slots.end_time, users.name AS staff_name, appointments.status
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
$nextStmt->execute([$studentId]);
$nextAppointment = $nextStmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | YIC Appointment System</title>
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
                <a href="my-appointments.php">My Appointments</a>
                <a class="is-active" href="profile.php">My Profile</a>
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
                <section class="panel-card profile-layout">
                    <article class="profile-summary">
                        <div class="avatar-circle"><?php echo htmlspecialchars(strtoupper(substr($student['name'], 0, 1))); ?></div>
                        <h2><?php echo htmlspecialchars($student['name']); ?></h2>
                        <span class="badge">Student</span>

                        <div class="mini-card">
                            <h3>Next Appointment</h3>
                            <?php if ($nextAppointment) { ?>
                                <p>Date: <?php echo htmlspecialchars($nextAppointment['date']); ?></p>
                                <p>
     <strong>Time:</strong>
    <?php echo htmlspecialchars(date("h:i A", strtotime($nextAppointment['start_time']))); ?>
    -
    <?php echo htmlspecialchars(date("h:i A", strtotime($nextAppointment['end_time']))); ?>
</p>
                                <p>Staff: <?php echo htmlspecialchars($nextAppointment['staff_name']); ?></p>
                                <p><span class="badge badge-success"><?php echo htmlspecialchars(ucfirst($nextAppointment['status'])); ?></span></p>
                            <?php } else { ?>
                                <p>No upcoming appointment.</p>
                            <?php } ?>
                        </div>
                    </article>

                    <section>
                        <form class="profile-form" method="POST" action="">
                            <h3>Profile Information</h3>
                            <div class="form-grid">
                                <div>
                                    <label for="profile-name">Full Name</label>
                                    <input id="profile-name" name="full_name" type="text" value="<?php echo htmlspecialchars($student['name']); ?>" required>
                                </div>
                                <div>
                                    <label for="profile-email">Email Address</label>
                                    <input id="profile-email" name="email" type="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                                </div>
                                <div>
                                    <label for="profile-id">Student ID</label>
                                    <input id="profile-id" name="student_id" type="text" value="<?php echo htmlspecialchars((string) $student['student_id']); ?>" required>
                                </div>
                                <div>
                                    <label for="profile-major">Major</label>
                                    <input id="profile-major" name="major" type="text" value="<?php echo htmlspecialchars($student['major']); ?>" required>
                                </div>
                                <div>
                                    <label for="profile-year">Year Level</label>
                                    <input id="profile-year" name="year_level" type="number" value="<?php echo htmlspecialchars((string) $student['year']); ?>" required>
                                </div>
                                <div>
                                    <label for="profile-phone">Phone Number</label>
                                    <input id="profile-phone" name="phone" type="tel" value="<?php echo htmlspecialchars($student['phone']); ?>" required>
                                </div>
                            </div>
                            <div class="button-row">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                            <hr>
                            <div class="profile-actions">
                                <a class="btn btn-danger" href="../logout.php">Logout</a>
                            </div>
                            <p class="form-message" style="color: <?php echo $messageColor; ?>;"><?php echo htmlspecialchars($message); ?></p>
                        </form>
                    </section>
                </section>
            </main>

            <footer class="site-footer">
                <p>&copy; 2026 Yanbu Industrial College.</p>
            </footer>
        </div>
    </div>
</body>
</html>