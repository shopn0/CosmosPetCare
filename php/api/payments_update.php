<?php
// Add CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Enable error reporting for debugging (comment out in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../includes/db_config.php';
require_once '../../includes/auth.php';
require_once '../../includes/api_utils.php';
require_once '../../includes/payments.php';
require_once '../../includes/appointments.php';
require_once '../../includes/jwt_helper.php';

// Get JSON data and extract payment_id for authorization
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);
// Accept legacy 'id' field
if (!isset($data['payment_id'])) {
    if (isset($data['id'])) {
        $data['payment_id'] = $data['id'];
    } else {
        sendError('Missing required field: payment_id', 400);
    }
}
$paymentId = (int)$data['payment_id'];

// Authenticate user with JWT
$userData = authenticateUser();
if (!$userData) {
    sendError('Unauthorized - Authentication failed', 401);
}

// Verify user authorization
if ($userData['role'] === 'customer') {
    // For customers, check if the payment belongs to them
    $conn = connectDB();
    $stmt = $conn->prepare("
        SELECT p.* FROM payments p
        JOIN appointments a ON p.appointment_id = a.id
        JOIN pets pt ON a.pet_id = pt.id
        WHERE p.id = ? AND pt.owner_id = ?
    ");
    $stmt->bind_param("ii", $paymentId, $userData['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        closeDB($conn);
        sendError('Payment not found or unauthorized access', 404);
    }
    $stmt->close();
}
else if ($userData['role'] !== 'admin' && $userData['role'] !== 'vet') {
    sendError('Unauthorized access', 403);
}

// Validate HTTP method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 405);
}

// Validate required fields (reuse $data)
if (!isset($data['payment_id']) || !isset($data['status'])) {
    sendError('Missing required fields: payment_id, status', 400);
}

$status = $data['status'];
$transactionId = isset($data['transaction_id']) ? $data['transaction_id'] : null;

// Validate status value
$allowedStatuses = ['pending', 'completed', 'refunded'];
if (!in_array($status, $allowedStatuses)) {
    sendError('Invalid status value. Allowed values: ' . implode(', ', $allowedStatuses), 400);
}

// Connect to database
$conn = connectDB();
if (!$conn) {
    sendError('Database connection failed', 500);
    exit;
}

// Get current payment details before update
$paymentDetails = getPaymentDetails($paymentId);
if (!$paymentDetails) {
    sendError('Payment not found', 404);
}

// Start transaction
$conn->begin_transaction();

try {
    // Update payment status
    $query = "UPDATE payments SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('si', $status, $paymentId);
    
    if (!$stmt->execute()) {
        throw new Exception("Database update error: " . $stmt->error);
    }
    
    // If amount is provided, update it
    if (isset($data['amount'])) {
        $amount = (float)$data['amount'];
        $stmt = $conn->prepare("UPDATE payments SET amount = ? WHERE id = ?");
        $stmt->bind_param("di", $amount, $paymentId);
        if (!$stmt->execute()) {
            throw new Exception('Failed to update amount');
        }
        $stmt->close();
    }

    // If transaction_id is provided, update it
    if ($transactionId) {
        $stmt = $conn->prepare("UPDATE payments SET transaction_id = ? WHERE id = ?");
        $stmt->bind_param("si", $transactionId, $paymentId);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update transaction ID');
        }
        
        $stmt->close();
    }
    
    // Update appointment status based on payment status
    $appointmentId = $paymentDetails['appointment_id'];
    
    if ($status === 'completed') {
        // When payment is completed, mark the appointment as completed
        if (!updateAppointmentStatus($appointmentId, 'completed')) {
            throw new Exception('Failed to update appointment status');
        }
    } 
    else if ($status === 'refunded') {
        // When payment is refunded, cancel the appointment
        if (!updateAppointmentStatus($appointmentId, 'cancelled')) {
            throw new Exception('Failed to cancel appointment');
        }
    }
    
    // Commit the transaction
    $conn->commit();
    
    // Get updated payment details
    $updatedPayment = getPaymentDetails($paymentId);
    
    // Return success response
    sendSuccess([
        'payment' => $updatedPayment
    ], 'Payment status updated successfully');
    
} catch (Exception $e) {
    // Rollback the transaction
    $conn->rollback();
    sendError($e->getMessage(), 500);
} finally {
    closeDB($conn);
}
?>