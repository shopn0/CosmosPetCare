<?php
// API endpoint for updating appointments with diagnosis and prescription (for vets)
require_once '../../includes/db_config.php';
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

// Check user role (only vet or admin can update appointments)
if (!checkUserRole(['vet', 'admin'])) {
    sendError('Access denied. Only vets or admins can update appointments', 403);
}

// Get request data
$data = getRequestData();

// Validate required fields
$requiredFields = ['appointment_id', 'diagnosis', 'prescription'];
validateRequiredFields($data, $requiredFields);

// Sanitize inputs
$appointmentId = (int)$data['appointment_id'];
$diagnosis = sanitizeInput($data['diagnosis']);
$prescription = sanitizeInput($data['prescription']);

// Check if the appointment exists and belongs to the vet
if ($userData['role'] === 'vet') {
    $conn = connectDB();
    $stmt = $conn->prepare("SELECT id FROM appointments WHERE id = ? AND vet_id = ?");
    $stmt->bind_param("ii", $appointmentId, $userData['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        closeDB($conn);
        sendError('You can only update your own appointments', 403);
    }
    
    $stmt->close();
    closeDB($conn);
}

// Update the appointment with diagnosis and prescription
if (updateDiagnosisAndPrescription($appointmentId, $diagnosis, $prescription)) {
    // Get the updated appointment details
    $appointmentDetails = getAppointmentDetails($appointmentId);
    
    // Return success response
    sendSuccess([
        'appointment' => $appointmentDetails
    ], 'Appointment updated successfully');
} else {
    sendError('Failed to update appointment', 500);
}