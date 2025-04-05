<?php
require('razorpay-php/Razorpay.php'); // Include Razorpay SDK
include 'db.php';
session_start(); // Start the session to access logged-in student details

use Razorpay\Api\Api;

$api_key = 'YOUR API KEU'; // Replace with your Razorpay API key
$api_secret = 'YOUR SECRET API KEY'; // Replace with your Razorpay API secret

$student = null;

// Check if the student is logged in
if (isset($_SESSION['student_id'])) {
    $student_id = $_SESSION['student_id'];

    // Fetch student details using student_id
    $stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        die("Student not found. Please log in again.");
    }
}

$form_submitted = false; // Track if the form has been submitted

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $form_submitted = true; // Mark the form as submitted
    $amount = $_POST['amount'] * 100; // Convert to paise (Razorpay uses paise)

    if (!$student) {
        die("Student not found. Please log in again.");
    }

    // Create Razorpay order
    $api = new Api($api_key, $api_secret);
    $order = $api->order->create([
        'receipt' => 'receipt_' . uniqid(),
        'amount' => $amount,
        'currency' => 'INR',
        'payment_capture' => 1 // Auto-capture payment
    ]);

    // Store order details in the database (optional)
    $order_id = $order['id'];

    // Render Razorpay Checkout
    echo "
    <form action='verify_payment.php' method='POST'>
        <script
            src='https://checkout.razorpay.com/v1/checkout.js'
            data-key='$api_key'
            data-amount='$amount'
            data-currency='INR'
            data-order_id='$order_id'
            data-buttontext='Pay with Razorpay'
            data-name='Fee Management System'
            data-description='Fee Payment'
            data-prefill.name='{$student['name']}'
            data-prefill.email='{$student['email']}'
            data-theme.color='#F37254'>
        </script>
        <input type='hidden' name='student_id' value='{$student['student_id']}'>
        <input type='hidden' name='amount' value='{$_POST['amount']}'>
        <button type='submit' style='display: none;'>Pay</button> <!-- Hidden fallback button -->
    </form>
    ";
}
?>

<!-- Payment Form -->
<?php if ($student && !$form_submitted): ?>
<form method="POST" action="">
    <label>Student ID:</label>
    <input type="text" name="student_id" value="<?php echo htmlspecialchars($student['student_id']); ?>" readonly><br>
    <label>Student Name:</label>
    <input type="text" name="student_name" value="<?php echo htmlspecialchars($student['name']); ?>" readonly><br>
    <label>Course:</label>
    <input type="text" name="course" value="<?php echo htmlspecialchars($student['course']); ?>" readonly><br>
    <label>Amount:</label>
    <input type="number" step="0.01" name="amount" required><br>
    <button type="submit">Pay with Razorpay</button>
</form>
<?php elseif ($form_submitted): ?>
<p>The payment form has been submitted. Please complete the payment using Razorpay.</p>
<?php else: ?>
<p>Please log in to access the payment form.</p>
<?php endif; ?>