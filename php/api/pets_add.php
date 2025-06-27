<?php
// API endpoint for adding a pet (for customers)
require_once '../../includes/db_config.php';
require_once '../../includes/pets.php';
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

// Check user role (only customer or admin can add pets)
if (!checkUserRole(['customer', 'admin'])) {
    sendError('Access denied. Only customers or admins can add pets', 403);
}

// Get request data
$data = getRequestData();

// Validate required fields
$requiredFields = ['name', 'type', 'breed', 'age', 'gender', 'color'];
validateRequiredFields($data, $requiredFields);

// Sanitize inputs
$name = sanitizeInput($data['name']);
$type = sanitizeInput($data['type']);
$breed = sanitizeInput($data['breed']);
$age = (int)$data['age'];
$gender = sanitizeInput($data['gender']);
$color = sanitizeInput($data['color']);

// Validate gender
$allowedGenders = ['male', 'female', 'unknown'];
if (!in_array($gender, $allowedGenders)) {
    sendError('Invalid gender. Allowed values: male, female, unknown', 400);
}

// Use the user's ID from JWT token or allow admin to specify owner_id
$ownerId = $userData['id'];
if ($userData['role'] === 'admin' && isset($data['owner_id'])) {
    $ownerId = (int)$data['owner_id'];
}

// Add the pet
$petId = addPet($name, $type, $breed, $age, $gender, $color, $ownerId);

if ($petId) {
    // Get the pet details to return
    $petDetails = getPetDetails($petId);
    
    // Return success response
    sendSuccess([
        'pet' => $petDetails
    ], 'Pet added successfully');
} else {
    sendError('Failed to add pet', 500);
}