<?php
require_once 'db_config.php';

// Function to create a new payment
function createPayment($appointmentId, $amount, $method, $transactionId = null) {
    $conn = connectDB();
    
    // Check if connection was successful
    if (!$conn) {
        error_log("Database connection failed in createPayment");
        return 0;
    }
    
    // Validate method against allowed values
    $allowedMethods = ['cash', 'card', 'bkash', 'nagad', 'rocket'];
    if (!in_array($method, $allowedMethods)) {
        error_log("Invalid payment method: $method. Using 'cash' as default.");
        $method = 'cash'; // Default to cash if invalid method is provided
    }
    
    try {
        $stmt = $conn->prepare("INSERT INTO payments (appointment_id, amount, status, method, transaction_id) 
                              VALUES (?, ?, 'pending', ?, ?)");
                              
        if (!$stmt) {
            error_log("Prepare statement failed: " . $conn->error);
            closeDB($conn);
            return 0;
        }
        
        $stmt->bind_param("idss", $appointmentId, $amount, $method, $transactionId);
        
        $result = $stmt->execute();
        
        if (!$result) {
            error_log("Execute statement failed: " . $stmt->error);
            $stmt->close();
            closeDB($conn);
            return 0;
        }
        
        $paymentId = $conn->insert_id;
        
        $stmt->close();
        closeDB($conn);
        
        return $paymentId;
    } catch (Exception $e) {
        error_log("Exception in createPayment: " . $e->getMessage());
        if (isset($stmt)) $stmt->close();
        closeDB($conn);
        return 0;
    }
}

// Function to update payment status
function updatePaymentStatus($paymentId, $status) {
    $conn = connectDB();
    
    $stmt = $conn->prepare("UPDATE payments SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $paymentId);
    
    $result = $stmt->execute();
    
    $stmt->close();
    closeDB($conn);
    
    return $result;
}

// Function to get payment details
function getPaymentDetails($paymentId) {
    $conn = connectDB();
    
    // Include owner_id to verify customer ownership
    $stmt = $conn->prepare("SELECT p.*, a.date AS appointment_date,
                           pet.name AS pet_name, u.name AS owner_name,
                           v.name AS vet_name, pet.owner_id AS owner_id
                           FROM payments p
                           JOIN appointments a ON p.appointment_id = a.id
                           JOIN pets pet ON a.pet_id = pet.id
                           JOIN users u ON pet.owner_id = u.id
                           JOIN users v ON a.vet_id = v.id
                           WHERE p.id = ?");
    $stmt->bind_param("i", $paymentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $payment = $result->fetch_assoc();
    
    $stmt->close();
    closeDB($conn);
    
    return $payment;
}

// Function to get payments by appointment
function getPaymentByAppointment($appointmentId) {
    $conn = connectDB();
    
    $stmt = $conn->prepare("SELECT * FROM payments WHERE appointment_id = ?");
    $stmt->bind_param("i", $appointmentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $payment = $result->fetch_assoc();
    
    $stmt->close();
    closeDB($conn);
    
    return $payment;
}

// Function to get payments by owner
function getPaymentsByOwner($ownerId) {
    $conn = connectDB();
    
    $stmt = $conn->prepare("SELECT p.*, a.date as appointment_date, 
                           pet.name as pet_name
                           FROM payments p
                           JOIN appointments a ON p.appointment_id = a.id
                           JOIN pets pet ON a.pet_id = pet.id
                           WHERE pet.owner_id = ?
                           ORDER BY p.payment_date DESC");
    $stmt->bind_param("i", $ownerId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $payments = [];
    while ($payment = $result->fetch_assoc()) {
        $payments[] = $payment;
    }
    
    $stmt->close();
    closeDB($conn);
    
    return $payments;
}

// Function to get all payments for admin
function getAllPayments() {
    $conn = connectDB();
    
    $stmt = $conn->prepare("SELECT p.*, a.date as appointment_date, 
                           pet.name as pet_name, u.name as owner_name,
                           v.name as vet_name
                           FROM payments p
                           JOIN appointments a ON p.appointment_id = a.id
                           JOIN pets pet ON a.pet_id = pet.id
                           JOIN users u ON pet.owner_id = u.id
                           JOIN users v ON a.vet_id = v.id
                           ORDER BY p.payment_date DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $payments = [];
    while ($payment = $result->fetch_assoc()) {
        $payments[] = $payment;
    }
    
    $stmt->close();
    closeDB($conn);
    
    return $payments;
}