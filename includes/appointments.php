<?php
require_once 'db_config.php';

// Function to create a new appointment
function createAppointment($petId, $vetId, $date, $reason) {
    $conn = connectDB();
    
    $stmt = $conn->prepare("INSERT INTO appointments (pet_id, vet_id, date, reason, status) 
                           VALUES (?, ?, ?, ?, 'pending')");
    $stmt->bind_param("iiss", $petId, $vetId, $date, $reason);
    
    $result = $stmt->execute();
    $appointmentId = $result ? $conn->insert_id : 0;
    
    $stmt->close();
    closeDB($conn);
    
    return $appointmentId;
}

// Function to get appointment details
function getAppointmentDetails($appointmentId) {
    $conn = connectDB();
    
    $stmt = $conn->prepare("SELECT a.*, p.name as pet_name, p.type as pet_type, p.breed as pet_breed,
                           u1.name as owner_name, u2.name as vet_name
                           FROM appointments a
                           JOIN pets p ON a.pet_id = p.id
                           JOIN users u1 ON p.owner_id = u1.id
                           JOIN users u2 ON a.vet_id = u2.id
                           WHERE a.id = ?");
    $stmt->bind_param("i", $appointmentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $appointment = $result->fetch_assoc();
    
    $stmt->close();
    closeDB($conn);
    
    return $appointment;
}

// Function to get appointment by ID
function getAppointmentById($appointmentId) {
    $conn = connectDB();
    
    $stmt = $conn->prepare("SELECT a.*, p.name as pet_name, p.type as pet_type, p.breed as pet_breed,
                           u1.name as owner_name, u2.name as vet_name
                           FROM appointments a
                           JOIN pets p ON a.pet_id = p.id
                           JOIN users u1 ON p.owner_id = u1.id
                           JOIN users u2 ON a.vet_id = u2.id
                           WHERE a.id = ?");
    $stmt->bind_param("i", $appointmentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $appointment = $result->fetch_assoc();
    
    $stmt->close();
    closeDB($conn);
    
    return $appointment;
}

// Function to get appointments by pet owner
function getAppointmentsByOwner($ownerId) {
    $conn = connectDB();
    
    $stmt = $conn->prepare("SELECT a.*, p.name as pet_name, u.name as vet_name
                           FROM appointments a
                           JOIN pets p ON a.pet_id = p.id
                           JOIN users u ON a.vet_id = u.id
                           WHERE p.owner_id = ?
                           ORDER BY a.date DESC");
    $stmt->bind_param("i", $ownerId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $appointments = [];
    while ($appointment = $result->fetch_assoc()) {
        $appointments[] = $appointment;
    }
    
    $stmt->close();
    closeDB($conn);
    
    return $appointments;
}

// Function to get appointments by vet
function getAppointmentsByVet($vetId) {
    $conn = connectDB();
    
    $stmt = $conn->prepare("SELECT a.*, p.name as pet_name, p.type as pet_type, 
                           p.breed as pet_breed, u.name as owner_name
                           FROM appointments a
                           JOIN pets p ON a.pet_id = p.id
                           JOIN users u ON p.owner_id = u.id
                           WHERE a.vet_id = ?
                           ORDER BY a.date ASC");
    $stmt->bind_param("i", $vetId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $appointments = [];
    while ($appointment = $result->fetch_assoc()) {
        $appointments[] = $appointment;
    }
    
    $stmt->close();
    closeDB($conn);
    
    return $appointments;
}

// Function to update appointment status
function updateAppointmentStatus($appointmentId, $status) {
    $conn = connectDB();
    
    $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $appointmentId);
    
    $result = $stmt->execute();
    
    $stmt->close();
    closeDB($conn);
    
    return $result;
}

// Function to update diagnosis and prescription
function updateDiagnosisAndPrescription($appointmentId, $diagnosis, $prescription) {
    $conn = connectDB();
    
    $stmt = $conn->prepare("UPDATE appointments 
                           SET diagnosis = ?, prescription = ?, status = 'completed' 
                           WHERE id = ?");
    $stmt->bind_param("ssi", $diagnosis, $prescription, $appointmentId);
    
    $result = $stmt->execute();
    
    $stmt->close();
    closeDB($conn);
    
    return $result;
}

// Function to get all appointments for admin
function getAllAppointments() {
    $conn = connectDB();
    
    $stmt = $conn->prepare("SELECT a.*, p.name as pet_name, p.type as pet_type,
                           u1.name as owner_name, u2.name as vet_name
                           FROM appointments a
                           JOIN pets p ON a.pet_id = p.id
                           JOIN users u1 ON p.owner_id = u1.id
                           JOIN users u2 ON a.vet_id = u2.id
                           ORDER BY a.date DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $appointments = [];
    while ($appointment = $result->fetch_assoc()) {
        $appointments[] = $appointment;
    }
    
    $stmt->close();
    closeDB($conn);
    
    return $appointments;
}

// Function to get pending appointments
function getPendingAppointments() {
    $conn = connectDB();
    
    $stmt = $conn->prepare("SELECT a.*, p.name as pet_name, p.type as pet_type,
                           u1.name as owner_name, u2.name as vet_name
                           FROM appointments a
                           JOIN pets p ON a.pet_id = p.id
                           JOIN users u1 ON p.owner_id = u1.id
                           JOIN users u2 ON a.vet_id = u2.id
                           WHERE a.status = 'pending'
                           ORDER BY a.date ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $appointments = [];
    while ($appointment = $result->fetch_assoc()) {
        $appointments[] = $appointment;
    }
    
    $stmt->close();
    closeDB($conn);
    
    return $appointments;
}