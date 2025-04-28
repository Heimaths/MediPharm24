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

$stmt = $db->prepare("SELECT id, salutation, first_name, last_name, email, address, postal_code, city, username, is_admin FROM kunden ORDER BY last_name, first_name");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['status' => 'success', 'users' => $users]); 