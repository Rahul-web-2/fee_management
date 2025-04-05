<?php
include 'db.php';
require('fpdf186/fpdf.php'); // Include the FPDF library

// Get student_id and payment_id from the GET request
$student_id = isset($_GET['student_id']) ? trim($_GET['student_id']) : '';
$payment_id = isset($_GET['payment_id']) ? trim($_GET['payment_id']) : '';

if ($student_id && $payment_id) {
    // Fetch student and specific payment details
    $stmt = $conn->prepare("SELECT s.name, s.student_id, s.course, p.amount_paid, p.payment_date, p.payment_id 
                            FROM students s 
                            JOIN payments p ON s.student_id = p.student_id 
                            WHERE s.student_id = ? AND p.payment_id = ?");
    $stmt->execute([$student_id, $payment_id]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($payment) {
        // Create a new PDF instance
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);

        // Add receipt title
        $pdf->Cell(0, 10, 'Fee Receipt', 0, 1, 'C');
        $pdf->Ln(10);

        // Add student details
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, 'Student Name: ' . $payment['name'], 0, 1);
        $pdf->Cell(0, 10, 'Student ID: ' . $payment['student_id'], 0, 1);
        $pdf->Cell(0, 10, 'Course: ' . $payment['course'], 0, 1);
        $pdf->Ln(10);

        // Add payment details
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Payment Details:', 0, 1);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, 'Payment ID: ' . $payment['payment_id'], 0, 1);
        $pdf->Cell(0, 10, 'Amount Paid: ' . $payment['amount_paid'], 0, 1);
        $pdf->Cell(0, 10, 'Payment Date: ' . $payment['payment_date'], 0, 1);

        $pdf->Ln(10);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Total Paid: ' . $payment['amount_paid'], 0, 1);

        // Output the PDF as a downloadable file
        $pdf->Output('D', 'Fee_Receipt_' . $payment['student_id'] . '_' . date('YmdHis') . '.pdf'); // 'D' forces download
        exit;
    } else {
        echo "No payment found for this student and payment ID.";
    }
} else {
    echo "Invalid request. Missing student ID or payment ID.";
}
?>