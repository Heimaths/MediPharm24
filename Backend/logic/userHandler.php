<?php
session_start();

include '../config/dbaccess.php'; // dataccess layer überlegen
include '../models/user.class.php';

$database = new Database();
$db = $database->connect();

$user = new User($db);

$action = $_POST['action'] ?? '';

header('Content-Type: application/json'); // Sicherstellen, dass JSON zurückkommt
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!$action) {
    echo json_encode(['status' => 'error', 'message' => 'Keine Aktion angegeben.']);
    exit();
}

if ($action == 'register') {
    // Registrierung: Hier übergibst du alle erforderlichen Felder an die User‑Klasse
    $user->salutation = $_POST['salutation'] ?? null;
    $user->first_name = $_POST['first_name'] ?? null;
    $user->last_name = $_POST['last_name'] ?? null;
    $user->address = $_POST['address'] ?? null;
    $user->postal_code = $_POST['postal_code'] ?? null;
    $user->city = $_POST['city'] ?? null;
    $user->email = $_POST['email'] ?? null;
    $user->username = $_POST['username'] ?? null;
    $user->password = $_POST['password'] ?? null;
    $user->payment_info = $_POST['payment_info'] ?? null;
    $user->is_admin = false;

    if ($user->register()) {
        echo json_encode(['status' => 'success', 'message' => 'User registered successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User registration failed']);
    }
} elseif ($action == 'login') {
    // Login: Hier werden nur Benutzername oder E-Mail und password übergeben
    $user->username = $_POST['identifier'] ?? '';  // FIX: richtiger Variablenname
    $user->password = $_POST['password'] ?? '';

    if ($user->login()) {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->username;
        $_SESSION['email'] = $user->email;
        $_SESSION['is_admin'] = (bool) $user->is_admin;

        echo json_encode(['status' => 'success', 'message' => 'Login successful']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Login failed']);
    }
} elseif ($action == 'getProfile') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Nicht eingeloggt']);
        exit();
    }
    $userId = $_SESSION['user_id'];
    $stmt = $db->prepare("SELECT address, email, payment_info, postal_code, city FROM kunden WHERE id = ?");
    $stmt->execute([$userId]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($userData) {
        echo json_encode(['status' => 'success', 'user' => $userData]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Benutzer nicht gefunden']);
    }
    exit();
} elseif ($action == 'updateProfile') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Nicht eingeloggt']);
        exit();
    }
    $userId = $_SESSION['user_id'];
    $address = $_POST['address'] ?? '';
    $email = $_POST['email'] ?? '';
    $payment_info = $_POST['payment_info'] ?? '';
    $postal_code = $_POST['postal_code'] ?? '';
    $city = $_POST['city'] ?? '';
    $password = $_POST['password'] ?? '';
    if (!empty($password)) {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $db->prepare("UPDATE kunden SET address=?, email=?, payment_info=?, postal_code=?, city=?, password=? WHERE id=?");
        $success = $stmt->execute([$address, $email, $payment_info, $postal_code, $city, $passwordHash, $userId]);
    } else {
        $stmt = $db->prepare("UPDATE kunden SET address=?, email=?, payment_info=?, postal_code=?, city=? WHERE id=?");
        $success = $stmt->execute([$address, $email, $payment_info, $postal_code, $city, $userId]);
    }
    if ($success) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Fehler beim Speichern']);
    }
    exit();
}
?>
