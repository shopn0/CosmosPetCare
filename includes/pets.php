<?php
require_once 'db_config.php';

// Function to add a new pet
function addPet($name, $type, $breed, $age, $gender, $color, $ownerId) {
    $conn = connectDB();
    
    $stmt = $conn->prepare("INSERT INTO pets (name, type, breed, age, gender, color, owner_id) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssisii", $name, $type, $breed, $age, $gender, $color, $ownerId);
    
    $result = $stmt->execute();
    $petId = $result ? $conn->insert_id : 0;
    
    $stmt->close();
    closeDB($conn);
    
    return $petId;
}

// Function to get all pets of a specific owner
function getPetsByOwner($ownerId) {
    $conn = connectDB();
    
    $stmt = $conn->prepare("SELECT * FROM pets WHERE owner_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $ownerId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $pets = [];
    while ($pet = $result->fetch_assoc()) {
        $pets[] = $pet;
    }
    
    $stmt->close();
    closeDB($conn);
    
    return $pets;
}

// Function to get a specific pet's details
function getPetDetails($petId) {
    $conn = connectDB();
    
    $stmt = $conn->prepare("SELECT p.*, u.name as owner_name FROM pets p 
                           JOIN users u ON p.owner_id = u.id 
                           WHERE p.id = ?");
    $stmt->bind_param("i", $petId);
    $stmt->execute();
    $result = $stmt->get_result();
    $pet = $result->fetch_assoc();
    
    $stmt->close();
    closeDB($conn);
    
    return $pet;
}

// Function to get a specific pet by ID
function getPetById($petId) {
    $conn = connectDB();
    
    $stmt = $conn->prepare("SELECT p.*, u.name as owner_name FROM pets p 
                           JOIN users u ON p.owner_id = u.id 
                           WHERE p.id = ?");
    $stmt->bind_param("i", $petId);
    $stmt->execute();
    $result = $stmt->get_result();
    $pet = $result->fetch_assoc();
    
    $stmt->close();
    closeDB($conn);
    
    return $pet;
}

// Function to update pet details
function updatePet($petId, $name, $type, $breed, $age, $gender, $color) {
    $conn = connectDB();
    
    $stmt = $conn->prepare("UPDATE pets 
                           SET name = ?, type = ?, breed = ?, age = ?, gender = ?, color = ? 
                           WHERE id = ?");
    $stmt->bind_param("sssissi", $name, $type, $breed, $age, $gender, $color, $petId);
    
    $result = $stmt->execute();
    
    $stmt->close();
    closeDB($conn);
    
    return $result;
}

// Function to delete a pet
function deletePet($petId, $ownerId) {
    $conn = connectDB();
    
    // Only allow deleting if it's the owner's pet or admin
    $stmt = $conn->prepare("DELETE FROM pets WHERE id = ? AND owner_id = ?");
    $stmt->bind_param("ii", $petId, $ownerId);
    
    $result = $stmt->execute();
    
    $stmt->close();
    closeDB($conn);
    
    return $result;
}

// Function to get all pets for admin
function getAllPets() {
    $conn = connectDB();
    
    $stmt = $conn->prepare("SELECT p.*, u.name as owner_name 
                           FROM pets p 
                           JOIN users u ON p.owner_id = u.id 
                           ORDER BY p.created_at DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $pets = [];
    while ($pet = $result->fetch_assoc()) {
        $pets[] = $pet;
    }
    
    $stmt->close();
    closeDB($conn);
    
    return $pets;
}