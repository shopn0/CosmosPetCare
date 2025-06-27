<?php
// API endpoint for fetching pets
require_once '../../includes/db_config.php';
require_once '../../includes/auth.php';
require_once '../../includes/api_utils.php';
require_once '../../includes/pets.php';
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

// Check if specific pet ID is requested
if (isset($_GET['id'])) {
    $petId = sanitizeInput($_GET['id']);
    $pet = getPetById($petId);
    
    if ($pet) {
        sendSuccess(['pet' => $pet], 'Pet found');
    } else {
        sendError('Pet not found', 404);
    }
}
// Check if pets for a specific owner are requested
else if (isset($_GET['owner_id'])) {
    $ownerId = sanitizeInput($_GET['owner_id']);
    
    // Security check - users can only view their own pets unless they're admin
    if ($userData['role'] !== 'admin' && $userData['id'] != $ownerId) {
        sendError('Unauthorized access', 403);
    }
    
    $pets = getPetsByOwner($ownerId);
    
    if ($pets) {
        sendSuccess(['pets' => $pets], 'Pets found');
    } else {
        sendError('No pets found', 404);
    }
}
// Return all pets (for admin/vet)
else {
    // Check if user has correct role
    if ($userData['role'] !== 'admin' && $userData['role'] !== 'vet') {
        sendError('Unauthorized access', 403);
    }
    
    $pets = getAllPets();
    
    if ($pets) {
        sendSuccess(['pets' => $pets], 'Pets found');
    } else {
        sendError('No pets found', 404);
    }
}