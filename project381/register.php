<?php
session_start();
include "includes/db.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name = trim($_POST['full_name']);
    $email =  trim($_POST['email']);
    $student_id = trim($_POST['student_id'] ?? '');
    $major = trim($_POST['major'] ?? '');
    $year = !empty($_POST['year_level']) ? $_POST['year_level'] : null;
    $role = $_POST['role'];
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

   if ($name == "" || $email == "" || $role == "" || $phone == "" || $password == "") {

    $error = "Please fill in all required fields";

} elseif (!in_array($role, ["student", "admin"])) {

    $error = "Invalid role selected";

} elseif (!ctype_digit($phone) || strlen($phone) != 10) {

    $error = "Phone number must be 10 digits";

} elseif ($role == "student" && ($student_id == "" || $major == "" || $year == "")) {

    $error = "Please fill in all student fields";

} elseif ($role == "student" && (!ctype_digit($student_id) || !in_array($year, ["1", "2", "3", "4"]))) {

    $error = "Please enter valid student information";

} elseif ($role == "student" && !str_ends_with($email, "@stu.yic.edu.sa")) {

    $error = "Student email must end with @stu.yic.edu.sa";

} elseif (
    $role == "admin" &&
    (
        !str_ends_with($email, "@yic.edu.sa") ||
        str_ends_with($email, "@stu.yic.edu.sa")
    )
) {

    $error = "Admin email must end with @yic.edu.sa";

    } elseif ($password != $confirm) {

        $error = "Passwords do not match";

    } else {

        $check = $pdo->prepare("
            SELECT id FROM users
            WHERE email = ?
        ");

    $check->execute([$email]);

    if ($check->fetch()) {

        $error = "Email already exists";

    } else {

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO users
            (name, email, password, role, student_id, major, year, phone)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $name,
            $email,
            $hashedPassword,
            $role,
            $student_id,
            $major,
            $year,
            $phone
        ]);

        header("Location: login.php");
        exit();
    }
}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | YIC Appointment System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
    <header class="topbar simple-topbar">
        <div class="brand">
            <span class="brand-mark">YIC</span>
            <div>
                <h1>Appointment Scheduling System</h1>
                <p>Yanbu Industrial College</p>
            </div>
        </div>
    </header>

    <main class="main-content">
        <section class="auth-card">
            <article class="auth-visual">
                <span class="eyebrow">Create Account</span>
                <h2>Join the YIC booking portal</h2>
                <p>Register using your university information and create your student account for appointment scheduling.</p>
            </article>

            <section class="auth-form-panel">
                <form class="auth-form" method="POST">
                    <h3>Sign Up</h3>
                    <label for="full-name">Full Name</label>
                   <input
    id="full-name"
    name="full_name"
    type="text"
    value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
    required
>

                    <label for="register-email">University Email</label>
                  <input
    id="register-email"
    name="email"
    type="email"
    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
    placeholder="Enter your university email"
    required
>
                            <label for="role">Role</label>
                            <select id="role" name="role">
                                <option value="student"
<?= (($_POST['role'] ?? '') == 'student') ? 'selected' : '' ?>>
Student
</option>

<option value="admin"
<?= (($_POST['role'] ?? '') == 'admin') ? 'selected' : '' ?>>
Admin
</option>
                            </select>
                       
                    <div class="form-grid" id="student-fields">

    <div>
        <label for="student-id">Student ID</label>
       <input
    id="student-id"
    name="student_id"
    type="text"
    pattern="[0-9]+"
    value="<?= htmlspecialchars($_POST['student_id'] ?? '') ?>"
    required
>
    </div>

    <div>
        <label for="major">Major</label>
       <input
    id="major"
    name="major"
    type="text"
    value="<?= htmlspecialchars($_POST['major'] ?? '') ?>"
    required
>
    </div>

</div>

                   <div class="form-grid">
    <div id="year-field">
        <label for="year-level">Year Level</label>

        <select id="year-level" name="year_level">

            <option value="">Select Year</option>
            <option value="1">1st Year</option>
            <option value="2">2nd Year</option>
            <option value="3">3rd Year</option>
            <option value="4">4th Year</option>

        </select>
    </div>
</div>

                    <label for="phone">Phone Number</label>
                    <input
    id="phone"
    name="phone"
    type="tel"
    pattern="[0-9]{10}"
    value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
    placeholder="05XXXXXXXX"
    required
>

                    <label for="register-password">Password</label>
                   <input
    id="register-password"
    name="password"
    type="password"
    minlength="8"
    required
>
<small>Password must be at least 8 characters.</small>

                    <label for="confirm-password">Confirm Password</label>
                    <input id="confirm-password" name="confirm_password" type="password" minlength="8" required>

                    <button type="submit" class="btn btn-primary btn-block">Sign Up</button>
                    <?php if(isset($error)): ?>
    <p class="form-message" role="alert">
        <?= htmlspecialchars($error) ?>
    </p>
<?php endif; ?>
                    <p class="muted-text">Already have an account? <a href="login.php">Log in</a></p>
                   
                </form>
            </section>
        </section>
    </main>

    <footer class="site-footer">
        <p>© 2026 Yanbu Industrial College.</p>
    </footer>

    <script src="assets/js/script.js"></script>
    <script>
const roleSelect = document.getElementById("role");
const studentFields = document.getElementById("student-fields");
const yearField = document.getElementById("year-field");

const studentIdInput = document.getElementById("student-id");
const majorInput = document.getElementById("major");
const yearInput = document.getElementById("year-level");

function toggleFields() {

    if (roleSelect.value === "admin") {

        studentFields.style.display = "none";
        yearField.style.display = "none";

        studentIdInput.required = false;
        majorInput.required = false;
        yearInput.required = false;

    } else {

        studentFields.style.display = "grid";
        yearField.style.display = "block";

        studentIdInput.required = true;
        majorInput.required = true;
        yearInput.required = true;
    }
}

toggleFields();

roleSelect.addEventListener("change", toggleFields);
</script>
</body>
</html>
