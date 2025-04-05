<?php
include 'db.php';
session_start(); // Start session for login tracking

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['register'])) {
        // Registration logic
        $name = trim($_POST['name']);
        $student_id = trim($_POST['student_id']);
        $email = trim($_POST['email']);
        $course = trim($_POST['course']);
        $password = password_hash(trim($_POST['password']), PASSWORD_BCRYPT); // Hash the password

        // Check if email or student_id already exists
        $stmt = $conn->prepare("SELECT * FROM students WHERE email = ? OR student_id = ?");
        $stmt->execute([$email, $student_id]);
        if ($stmt->rowCount() > 0) {
            echo "<p style='color: red;'>Email or Student ID already exists!</p>";
        } else {
            // Insert the new student
            $stmt = $conn->prepare("INSERT INTO students (name, student_id, email, course, password) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $student_id, $email, $course, $password]);

            echo "<p style='color: green;'>Student registered successfully!</p>";
        }
    } elseif (isset($_POST['login'])) {
        // Login logic
        $student_id = trim($_POST['student_id']);
        $password = trim($_POST['password']);

        // Check if student_id exists
        $stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
        $stmt->execute([$student_id]);
        $student = $stmt->fetch();

        if ($student && password_verify($password, $student['password'])) {
            // Store student details in session
            $_SESSION['student_id'] = $student['student_id'];
            $_SESSION['name'] = $student['name'];
            $_SESSION['course'] = $student['course'];

            // Redirect to payment.php
            header("Location: payment.php");
            exit();
        } else {
            echo "<p style='color: red;'>Invalid Student ID or Password!</p>";
        }
    }
}
?>

<!-- Registration Form -->
<h2>Register</h2>
<form method="POST" action="">
    <input type="hidden" name="register" value="1">
    <label>Name:</label>
    <input type="text" name="name" required><br>
    <label>Student ID:</label>
    <input type="text" name="student_id" required><br>
    <label>Email:</label>
    <input type="email" name="email" required><br>
    <label>Course:</label>
    <input type="text" name="course" required><br>
    <label>Password:</label>
    <input type="password" name="password" required><br>
    <button type="submit">Register</button>
</form>

<!-- Login Form -->
<h2>Already Registered? Login</h2>
<form method="POST" action="">
    <input type="hidden" name="login" value="1">
    <label>Student ID:</label>
    <input type="text" name="student_id" required><br>
    <label>Password:</label>
    <input type="password" name="password" required><br>
    <button type="submit">Login</button>
</form>