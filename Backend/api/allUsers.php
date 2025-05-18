<?php
session_start();
header('Content-Type: application/json');
require_once '../config/dbaccess.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    echo json_encode(['status' => 'error', 'message' => 'Keine Berechtigung']);
    exit();
}

$database = new Database();
$db = $database->connect();

// Suchbegriff aus GET-Parameter holen
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if (!empty($search)) {
    // Suche in verschiedenen Feldern
    $stmt = $db->prepare("SELECT id, salutation, first_name, last_name, email, address, postal_code, city, username, is_admin, is_active 
                         FROM kunden 
                         WHERE first_name LIKE :search 
                         OR last_name LIKE :search 
                         OR email LIKE :search 
                         OR username LIKE :search 
                         ORDER BY id ASC");
    $searchTerm = "%{$search}%";
    $stmt->bindParam(':search', $searchTerm);
} else {
    // Alle Benutzer ohne Suche
    $stmt = $db->prepare("SELECT id, salutation, first_name, last_name, email, address, postal_code, city, username, is_admin, is_active 
                         FROM kunden 
                         ORDER BY id ASC");
}

$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['status' => 'success', 'users' => $users]); 