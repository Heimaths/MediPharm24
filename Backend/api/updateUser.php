<?php
session_start();
header('Content-Type: application/json');
require_once '../config/dbaccess.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    echo json_encode(['status' => 'error', 'message' => 'Keine Berechtigung']);
    exit();
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Ungültige User-ID']);
    exit();
}

$salutation = $_POST['salutation'] ?? '';
$first_name = $_POST['first_name'] ?? '';
$last_name = $_POST['last_name'] ?? '';
$email = $_POST['email'] ?? '';
$address = $_POST['address'] ?? '';
$postal_code = $_POST['postal_code'] ?? '';
$city = $_POST['city'] ?? '';
$username = $_POST['username'] ?? '';
$is_admin = isset($_POST['is_admin']) ? (int)$_POST['is_admin'] : 0;
$password = $_POST['password'] ?? '';

$database = new Database();
$db = $database->connect();

try {
    if (!empty($password)) {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $db->prepare("UPDATE kunden SET salutation=?, first_name=?, last_name=?, email=?, address=?, postal_code=?, city=?, username=?, is_admin=?, password=? WHERE id=?");
        $success = $stmt->execute([$salutation, $first_name, $last_name, $email, $address, $postal_code, $city, $username, $is_admin, $passwordHash, $id]);
    } else {
        $stmt = $db->prepare("UPDATE kunden SET salutation=?, first_name=?, last_name=?, email=?, address=?, postal_code=?, city=?, username=?, is_admin=? WHERE id=?");
        $success = $stmt->execute([$salutation, $first_name, $last_name, $email, $address, $postal_code, $city, $username, $is_admin, $id]);
    }
    if ($success) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Fehler beim Speichern']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Fehler: ' . $e->getMessage()]);
} 