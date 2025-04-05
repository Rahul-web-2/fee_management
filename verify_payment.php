<?php
require('razorpay-php/Razorpay.php'); // Include Razorpay SDK
include 'db.php';

use Razorpay\Api\Api;

$api_key = 'YOUR API KEU'; // Replace with your Razorpay API key
$api_secret = 'YOUR SECRET API KEY'; // Replace with your Razorpay API secret

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_POST['student_id'];
    $amount = $_POST['amount']; // Amount in rupees
    $payment_id = $_POST['razorpay_payment_id'];
    $order_id = $_POST['razorpay_order_id'];
    $signature = $_POST['razorpay_signature'];

    // Verify payment signature
    $api = new Api($api_key, $api_secret);
    $attributes = [
        'razorpay_order_id' => $order_id,
        'razorpay_payment_id' => $payment_id,
        'razorpay_signature' => $signature
    ];

    try {
        $api->utility->verifyPaymentSignature($attributes);

        // Fetch student name from the database
        $student_stmt = $conn->prepare("SELECT name FROM students WHERE student_id = ?");
        $student_stmt->execute([$student_id]);
        $student = $student_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            die("Student not found. Please check your Student ID.");
        }

        // Payment is successful, store it in the database
        $stmt = $conn->prepare("INSERT INTO payments (name, student_id, amount_paid, payment_date, payment_id) VALUES (?, ?, ?, NOW(), ?)");
        $stmt->execute([$student['name'], $student_id, $amount, $payment_id]);

        // Display payment ID and download button
        echo "<h2>Payment Successful!</h2>";
        echo "<p>Payment ID: <strong>" . htmlspecialchars($payment_id) . "</strong></p>";
        echo "<form method='GET' action='receipt.php'>";
        echo "<input type='hidden' name='student_id' value='" . htmlspecialchars($student_id) . "'>";
        echo "<input type='hidden' name='payment_id' value='" . htmlspecialchars($payment_id) . "'>";
        echo "<button type='submit'>Download Receipt</button>";
        echo "</form>";
    } catch (Exception $e) {
        echo "Payment verification failed: " . $e->getMessage();
    }
}
?>