<?php
// API endpoint for creating payments (for customers)
require_once '../../includes/db_config.php';
require_once '../../includes/payments.php';
require_once '../../includes/appointments.php';
require_once '../../includes/api_utils.php';
require_once '../../includes/jwt_helper.php';

// Set CORS headers
setCorsHeaders();

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 405);
}

// Authenticate user
$userData = authenticateUser();
if (!$userData) {
    sendError('Unauthorized', 401);
}

// Check user role (only customer or admin can create payments)
if (!checkUserRole(['customer', 'admin'])) {
    sendError('Access denied. Only customers or admins can create payments', 403);
}

// Get request data
$data = getRequestData();

// Validate required fields
$requiredFields = ['appointment_id', 'amount', 'method'];
validateRequiredFields($data, $requiredFields);

// Sanitize inputs
$appointmentId = (int)$data['appointment_id'];
$amount = (float)$data['amount'];
$method = sanitizeInput($data['method']);
$transactionId = isset($data['transaction_id']) ? sanitizeInput($data['transaction_id']) : null;

// Validate payment method
$allowedMethods = ['cash', 'card', 'bkash', 'nagad', 'rocket'];
if (!in_array($method, $allowedMethods)) {
    sendError('Invalid payment method. Allowed values: cash, card, bkash, nagad, rocket', 400);
}

// Check if the appointment exists and belongs to the customer
if ($userData['role'] === 'customer') {
    $conn = connectDB();
    $stmt = $conn->prepare("SELECT a.id 
                           FROM appointments a
                           JOIN pets p ON a.pet_id = p.id
                           WHERE a.id = ? AND p.owner_id = ?");
    $stmt->bind_param("ii", $appointmentId, $userData['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        closeDB($conn);
        sendError('You can only make payments for your own appointments', 403);
    }
    
    $stmt->close();
    closeDB($conn);
}

// Check if payment already exists for this appointment
$existingPayment = getPaymentByAppointment($appointmentId);
if ($existingPayment) {
    sendError('Payment already exists for this appointment', 400);
}

// Start a transaction
$conn = connectDB();
$conn->begin_transaction();

try {
    // Create the payment
    $paymentId = createPayment($appointmentId, $amount, $method, $transactionId);
    
    if (!$paymentId) {
        throw new Exception('Failed to create payment');
    }
    
    // Update appointment status to confirmed if payment is successful
    // For cash payments, mark as pending until verified
    $paymentStatus = ($method === 'cash') ? 'pending' : 'completed';
    updatePaymentStatus($paymentId, $paymentStatus);
    
    // If payment is completed, update the appointment status to confirmed
    if ($paymentStatus === 'completed') {
        if (!updateAppointmentStatus($appointmentId, 'confirmed')) {
            throw new Exception('Failed to update appointment status');
        }
    }
    
    // Commit the transaction
    $conn->commit();
    
    // Get the payment details to return
    $paymentDetails = getPaymentDetails($paymentId);
    
    // Return success response
    sendSuccess([
        'payment' => $paymentDetails
    ], 'Payment created successfully');
    
} catch (Exception $e) {
    // Rollback the transaction if something went wrong
    $conn->rollback();
    sendError($e->getMessage(), 500);
} finally {
    closeDB($conn);
}