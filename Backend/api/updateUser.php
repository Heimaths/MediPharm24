<?php
session_start();
header('Content-Type: application/json');
require_once '../config/dbaccess.php';
require_once '../models/user.class.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    echo json_encode(['status' => 'error', 'message' => 'Keine Berechtigung']);
    exit();
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Ungültige User-ID']);
    exit();
}

$database = new Database();
$db = $database->connect();
$user = new User($db);

// Setze alle Benutzerdaten
$user->id = $id;
$user->salutation = $_POST['salutation'] ?? '';
$user->first_name = $_POST['first_name'] ?? '';
$user->last_name = $_POST['last_name'] ?? '';
$user->email = $_POST['email'] ?? '';
$user->address = $_POST['address'] ?? '';
$user->postal_code = $_POST['postal_code'] ?? '';
$user->city = $_POST['city'] ?? '';
$user->username = $_POST['username'] ?? '';
$user->is_admin = isset($_POST['is_admin']) ? (int)$_POST['is_admin'] : 0;
$user->is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
$user->password = $_POST['password'] ?? '';

// Führe das Update durch
$result = $user->update();
if ($result['success']) {
    echo json_encode(['status' => 'success', 'message' => $result['message']]);
} else {
    echo json_encode(['status' => 'error', 'message' => $result['message']]);
} 