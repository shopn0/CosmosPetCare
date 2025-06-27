<?php
// API endpoint for viewing pending appointments (for vets)
require_once '../../includes/db_config.php';
require_once '../../includes/appointments.php';
require_once '../../includes/api_utils.php';
require_once '../../includes/jwt_helper.php';

// Set CORS headers
setCorsHeaders();

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Method not allowed', 405);
}

// Authenticate user
$userData = authenticateUser();
if (!$userData) {
    sendError('Unauthorized', 401);
}

// Check user role (only vet or admin can view pending appointments)
if (!checkUserRole(['vet', 'admin'])) {
    sendError('Access denied. Only vets or admins can view pending appointments', 403);
}

// Get appointments based on user role
$appointments = [];
if ($userData['role'] === 'vet') {
    // For vets, get only their pending appointments
    $conn = connectDB();
    $stmt = $conn->prepare("SELECT a.*, p.name as pet_name, p.type as pet_type, 
                           p.breed as pet_breed, u.name as owner_name
                           FROM appointments a
                           JOIN pets p ON a.pet_id = p.id
                           JOIN users u ON p.owner_id = u.id
                           WHERE a.vet_id = ? AND a.status = 'pending'
                           ORDER BY a.date ASC");
    $stmt->bind_param("i", $userData['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($appointment = $result->fetch_assoc()) {
        $appointments[] = $appointment;
    }
    
    $stmt->close();
    closeDB($conn);
} else if ($userData['role'] === 'admin') {
    // For admins, get all pending appointments
    $appointments = getPendingAppointments();
}

// Return success response
sendSuccess([
    'appointments' => $appointments
], 'Pending appointments retrieved successfully');