<?php
require('razorpay-php/Razorpay.php'); // Include Razorpay SDK
include 'db.php';

use Razorpay\Api\Api;

$api_key = 'YOUR_API_KEY'; // Replace with your Razorpay API key
$api_secret = 'YOUR_API_SECRET'; // Replace with your Razorpay API secret

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_POST['student_id'];
    $amount = $_POST['amount'] * 100; // Convert to paise (Razorpay uses paise)

    // Fetch student details using student_id
    $stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        die("Student not found. Please check your Student ID.");
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

    // Redirect to Razorpay Checkout
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
        <input type='hidden' name='student_id' value='$student_id'>
        <input type='hidden' name='amount' value='{$_POST['amount']}'>
        <button type='submit' style='display: none;'>Pay</button> <!-- Hidden fallback button -->
    </form>
    ";
}
?>

<!-- Payment Form -->
<form method="POST" action="">
    <label>Student ID:</label>
    <input type="text" name="student_id" required><br>
    <label>Amount:</label>
    <input type="number" step="0.01" name="amount" required><br>
    <button type="submit">Pay with Razorpay</button>
</form>