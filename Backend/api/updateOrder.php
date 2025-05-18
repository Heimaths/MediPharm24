<?php
session_start();
header('Content-Type: application/json');

// Überprüfe Admin-Berechtigung
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Keine Berechtigung']);
    exit;
}

require_once '../config/dbaccess.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Nur POST-Anfragen erlaubt']);
    exit;
}

if (!isset($_POST['order_id']) || !isset($_POST['status'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Erforderliche Felder fehlen']);
    exit;
}

$orderId = (int)$_POST['order_id'];
$status = $_POST['status'];

// Validiere Status
$allowedStatuses = ['offen', 'bestätigt', 'bezahlt', 'versendet', 'storniert'];
if (!in_array($status, $allowedStatuses)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Ungültiger Status']);
    exit;
}

try {
    $database = new Database();
    $pdo = $database->connect();

    // Bestellung aktualisieren
    $stmt = $pdo->prepare("
        UPDATE bestellungen 
        SET status = ?
        WHERE id = ?
    ");
    $stmt->execute([$status, $orderId]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Bestellung nicht gefunden']);
        exit;
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Bestellung wurde aktualisiert'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Datenbankfehler: ' . $e->getMessage()
    ]);
}
?> 