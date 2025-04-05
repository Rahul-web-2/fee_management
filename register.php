<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['register'])) {
        // Registration logic
        $name = $_POST['name'];
        $student_id = $_POST['student_id'];
        $email = $_POST['email'];
        $course = $_POST['course'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password

        // Check if email or student_id already exists
        $stmt = $conn->prepare("SELECT * FROM students WHERE email = ? OR student_id = ?");
        $stmt->execute([$email, $student_id]);
        if ($stmt->rowCount() > 0) {
            echo "Email or Student ID already exists!";
        } else {
            // Insert the new student
            $stmt = $conn->prepare("INSERT INTO students (name, student_id, email, course, password) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $student_id, $email, $course, $password]);

            echo "Student registered successfully!";
        }
    } elseif (isset($_POST['login'])) {
        // Login logic
        $student_id = $_POST['student_id'];
        $password = $_POST['password'];

        // Check if student_id exists
        $stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
        $stmt->execute([$student_id]);
        $student = $stmt->fetch();

        if ($student && password_verify($password, $student['password'])) {
            echo "Login successful! Welcome, " . htmlspecialchars($student['name']) . ".";
        } else {
            echo "Invalid Student ID or Password!";
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