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
require_once '../../includes/jwt_helper.php';

// Connect to database
$conn = connectDB();
if (!$conn) {
    sendError('Database connection failed', 500);
}

// Authenticate user with JWT
$userData = authenticateUser();
if (!$userData) {
    // Debug log
    error_log("JWT Authentication failed in payments_view.php. Headers: " . print_r(getAuthorizationHeader(), true));
    sendError('Unauthorized - Authentication failed', 401);
}

// Verify the user is authorized
if ($userData['role'] === 'customer') {
    // For customers, only allow them to view their own payments
    $query = "
        SELECT 
            p.id AS id,
            p.id AS invoice_number,
            p.payment_date AS date,
            p.amount,
            p.status,
            p.method,
            p.transaction_id,
            u.name as owner_name,
            u.email as owner_email,
            u.phone as owner_phone,
            pt.name as pet_name,
            pt.type as pet_type,
            a.reason as description,
            a.date as appointment_date,
            v.name as vet_name,
            CASE WHEN a.reason IS NOT NULL THEN a.reason ELSE 'Veterinary Service' END as service_type
        FROM 
            payments p
        JOIN 
            appointments a ON p.appointment_id = a.id
        JOIN 
            pets pt ON a.pet_id = pt.id
        JOIN 
            users u ON pt.owner_id = u.id
        LEFT JOIN
            users v ON a.vet_id = v.id
        WHERE 
            pt.owner_id = ?
        ORDER BY 
            p.payment_date DESC
    ";
    
    // Check if a specific payment ID was requested
    if (isset($_GET['id'])) {
        $paymentId = (int)$_GET['id'];
        $payment = getPaymentDetails($paymentId);
        
        // Check if payment belongs to this customer
        if ($payment && $payment['owner_id'] == $userData['id']) {
            sendSuccess(['payment' => $payment], 'Payment details retrieved successfully');
            exit;
        } else {
            sendError('Payment not found or unauthorized', 404);
            exit;
        }
    }
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userData['id']);
    $stmt->execute();
    $result = $stmt->get_result();
} else if ($userData['role'] === 'admin' || $userData['role'] === 'vet') {
    // For admins and vets, allow viewing all payments
    $query = "
        SELECT 
            p.*,
            u.name as owner_name,
            u.email as owner_email,
            u.phone as owner_phone,
            pt.name as pet_name,
            pt.type as pet_type,
            a.reason as description,
            a.date as appointment_date,
            v.name as vet_name,
            CASE WHEN a.reason IS NOT NULL THEN a.reason ELSE 'Veterinary Service' END as service_type
        FROM 
            payments p
        JOIN 
            appointments a ON p.appointment_id = a.id
        JOIN 
            pets pt ON a.pet_id = pt.id
        JOIN 
            users u ON pt.owner_id = u.id
        LEFT JOIN
            users v ON a.vet_id = v.id
        ORDER BY 
            p.payment_date DESC
    ";
    
    // Check if a specific payment ID was requested
    if (isset($_GET['id'])) {
        $paymentId = (int)$_GET['id'];
        $payment = getPaymentDetails($paymentId);
        
        if ($payment) {
            sendSuccess(['payment' => $payment], 'Payment details retrieved successfully');
            exit;
        } else {
            sendError('Payment not found', 404);
            exit;
        }
    }
    
    $result = $conn->query($query);
} else {
    error_log("Unauthorized access attempt to payments_view.php by user with role: " . $userData['role']);
    sendError('Unauthorized access', 403);
    exit;
}

if (!$result) {
    error_log("Database query error: " . $conn->error);
    sendError('Database query error', 500);
    exit;
}

$payments = [];
while ($row = $result->fetch_assoc()) {
    $payments[] = $row;
}

// Return success response
sendSuccess(['payments' => $payments], 'Payments retrieved successfully');

// Close the database connection
closeDB($conn);

/**
 * Generate dummy payments for fallback
 * 
 * @param int $count Number of payments to generate
 * @return array Array of dummy payments
 */
function generateDummyPayments($count = 10) {
    $payments = [];
    $statuses = ['completed', 'pending'];
    $methods = ['credit card', 'cash', 'bank transfer'];
    $ownerNames = ['John Doe', 'Jane Smith', 'Robert Johnson', 'Emily Davis', 'Michael Wilson'];
    $petNames = ['Max', 'Bella', 'Charlie', 'Luna', 'Bailey'];
    $petTypes = ['Dog', 'Cat', 'Bird', 'Rabbit', 'Hamster'];
    $serviceTypes = ['Checkup', 'Vaccination', 'Surgery', 'Grooming', 'Dental Care'];
    
    for ($i = 0; $i < $count; $i++) {
        $status = $statuses[array_rand($statuses)];
        $date = date('Y-m-d H:i:s', strtotime('-' . rand(1, 30) . ' days'));
        $amount = rand(50, 200) + (rand(0, 99) / 100);
        $ownerIndex = $i % count($ownerNames);
        
        $payments[] = [
            'id' => $i + 1,
            'customer_id' => $i + 1,
            'appointment_id' => $i + 1,
            'amount' => $amount,
            'method' => $methods[array_rand($methods)],
            'status' => $status,
            'payment_date' => $date,
            'owner_name' => $ownerNames[$ownerIndex],
            'owner_email' => strtolower(str_replace(' ', '.', $ownerNames[$ownerIndex])) . '@example.com',
            'owner_phone' => '+1 ' . rand(100, 999) . '-' . rand(100, 999) . '-' . rand(1000, 9999),
            'pet_name' => $petNames[$i % count($petNames)],
            'pet_type' => $petTypes[$i % count($petTypes)],
            'description' => 'Pet ' . $serviceTypes[$i % count($serviceTypes)] . ' visit',
            'service_type' => $serviceTypes[$i % count($serviceTypes)]
        ];
    }
    
    return $payments;
}
?>