<?php
// API endpoint for fetching appointments
require_once '../../includes/db_config.php';
require_once '../../includes/auth.php';
require_once '../../includes/api_utils.php';
require_once '../../includes/appointments.php';
require_once '../../includes/jwt_helper.php';

// Set CORS headers
setCorsHeaders();

// Authenticate user with JWT
$userData = authenticateUser();
if (!$userData) {
    sendError('Unauthorized', 401);
}

// Get request data
$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'GET') {
    sendError('Method not allowed', 405);
}

// Check if specific appointment ID is requested
if (isset($_GET['id'])) {
    $appointmentId = sanitizeInput($_GET['id']);
    $appointment = getAppointmentById($appointmentId);
    
    if ($appointment) {
        sendSuccess(['appointment' => $appointment], 'Appointment found');
    } else {
        sendError('Appointment not found', 404);
    }
}
// Check if appointments for a specific owner are requested
else if (isset($_GET['owner_id'])) {
    $ownerId = sanitizeInput($_GET['owner_id']);
    
    // Security check - users can only view their own appointments unless they're admin
    if ($userData['role'] !== 'admin' && $userData['id'] != $ownerId) {
        sendError('Unauthorized access', 403);
    }
    
    $appointments = getAppointmentsByOwner($ownerId);
    
    if ($appointments) {
        sendSuccess(['appointments' => $appointments], 'Appointments found');
    } else {
        sendError('No appointments found', 404);
    }
}
// Return all appointments (for admin/vet)
else {
    // Check if user has correct role
    if ($userData['role'] !== 'admin' && $userData['role'] !== 'vet') {
        sendError('Unauthorized access', 403);
    }
    
    $appointments = getAllAppointments();
    
    if ($appointments) {
        sendSuccess(['appointments' => $appointments], 'Appointments found');
    } else {
        sendError('No appointments found', 404);
    }
}