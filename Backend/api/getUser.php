<?php
session_start();
header('Content-Type: application/json');
require_once '../config/dbaccess.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    echo json_encode(['status' => 'error', 'message' => 'Keine Berechtigung']);
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Ungültige User-ID']);
    exit();
}

$database = new Database();
$db = $database->connect();

$stmt = $db->prepare("SELECT id, salutation, first_name, last_name, email, address, postal_code, city, username, is_admin, is_active FROM kunden WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo json_encode(['status' => 'success', 'user' => $user]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'User nicht gefunden']);
} 