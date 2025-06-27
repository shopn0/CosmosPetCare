<?php
// API endpoint for booking appointments (for customers)
require_once '../../includes/db_config.php';
require_once '../../includes/appointments.php';
require_once '../../includes/pets.php';
require_once '../../includes/api_utils.php';
require_once '../../includes/jwt_helper.php';
require_once '../../includes/payments.php'; // Add payments include

// Prevent PHP errors from corrupting JSON output
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Check user role (only customer or admin can book appointments)
if (!checkUserRole(['customer', 'admin'])) {
    sendError('Access denied. Only customers or admins can book appointments', 403);
}

// Get request data
$data = getRequestData();

// Validate required fields
$requiredFields = ['pet_id', 'vet_id', 'date', 'reason'];
validateRequiredFields($data, $requiredFields);

// Sanitize inputs
$petId = (int)$data['pet_id'];
$vetId = (int)$data['vet_id'];
$date = sanitizeInput($data['date']);
$reason = sanitizeInput($data['reason']);

// Get optional notes
$notes = isset($data['notes']) ? sanitizeInput($data['notes']) : '';

// Validate date format (Accept both YYYY-MM-DD HH:MM:SS and YYYY-MM-DD HH:MM)
if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}(:\d{2})?$/', $date)) {
    sendError('Invalid date format. Use YYYY-MM-DD HH:MM or YYYY-MM-DD HH:MM:SS', 400);
}

// Add seconds if they're missing from the date format
if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $date)) {
    $date .= ':00';
}

try {
    // Check if the pet belongs to the customer
    if ($userData['role'] === 'customer') {
        $conn = connectDB();
        if (!$conn) {
            throw new Exception("Database connection failed");
        }
        
        $stmt = $conn->prepare("SELECT id FROM pets WHERE id = ? AND owner_id = ?");
        $stmt->bind_param("ii", $petId, $userData['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            closeDB($conn);
            sendError('You can only book appointments for your own pets', 403);
        }
        
        $stmt->close();
        closeDB($conn);
    }

    // Check if the vet exists and is a vet
    $conn = connectDB();
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    
    $stmt = $conn->prepare("SELECT id FROM users WHERE id = ? AND role = 'vet'");
    $stmt->bind_param("i", $vetId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        closeDB($conn);
        sendError('Invalid vet ID', 400);
    }

    $stmt->close();

    // Check if the vet already has an appointment at the same time
    $stmt = $conn->prepare("SELECT id FROM appointments WHERE vet_id = ? AND date = ? AND status != 'cancelled'");
    $stmt->bind_param("is", $vetId, $date);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt->close();
        closeDB($conn);
        sendError('This vet already has an appointment at the selected time. Please choose a different time.', 400);
    }

    $stmt->close();
    closeDB($conn);

    // Create the appointment with a new connection
    $conn = connectDB();
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    
    // Start a transaction
    $conn->begin_transaction();

    // Create the appointment (omit notes column to match database schema)
    $stmt = $conn->prepare("INSERT INTO appointments (pet_id, vet_id, date, reason, status) 
                        VALUES (?, ?, ?, ?, 'pending')");
    $stmt->bind_param("iiss", $petId, $vetId, $date, $reason);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to create appointment: " . $stmt->error);
    }
    
    $appointmentId = $conn->insert_id;
    $stmt->close();
    
    // Create payment record with pending status
    $defaultAmount = 1500.00; // $15.00 default consultation fee
    
    $stmt = $conn->prepare("INSERT INTO payments (appointment_id, amount, status, method, transaction_id) 
                        VALUES (?, ?, 'pending', 'cash', NULL)");
    
    if (!$stmt) {
        throw new Exception("Failed to prepare payment statement: " . $conn->error);
    }
    
    $stmt->bind_param("id", $appointmentId, $defaultAmount);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to create payment record: " . $stmt->error);
    }
    
    $paymentId = $conn->insert_id;
    $stmt->close();
    
    // Commit the transaction
    $conn->commit();
    
    // Get the appointment details to return (using a new connection)
    closeDB($conn);
    $appointmentDetails = getAppointmentDetails($appointmentId);
    
    if (!$appointmentDetails) {
        throw new Exception("Failed to retrieve appointment details");
    }
    
    // Add payment info to the response
    $appointmentDetails['payment_id'] = $paymentId;
    $appointmentDetails['payment_amount'] = $defaultAmount;
    $appointmentDetails['payment_status'] = 'pending';
    
    // Return success response
    sendSuccess([
        'appointment' => $appointmentDetails
    ], 'Appointment booked successfully with pending payment');
    
} catch (Exception $e) {
    // Log error details for debugging
    error_log('appointments_book.php Error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    // If there's an active transaction, roll it back
    if (isset($conn) && $conn) {
        $conn->rollback();
        closeDB($conn);
    }
    sendError($e->getMessage(), 500);
}