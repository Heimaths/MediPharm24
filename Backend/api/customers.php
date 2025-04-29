<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Überprüfen ob Benutzer Admin ist
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Nicht autorisiert']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Alle Kunden abrufen
        $stmt = $pdo->prepare("
            SELECT k.*, COUNT(b.bestellungs_id) as anzahl_bestellungen 
            FROM kunden k 
            LEFT JOIN bestellungen b ON k.kunden_id = b.kunden_id 
            GROUP BY k.kunden_id
        ");
        $stmt->execute();
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($customers);
        break;

    case 'PUT':
        // Kunde deaktivieren
        $data = json_decode(file_get_contents('php://input'), true);
        $customerId = $data['customer_id'] ?? null;
        
        if (!$customerId) {
            http_response_code(400);
            echo json_encode(['error' => 'Kunden-ID erforderlich']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE kunden SET is_active = FALSE WHERE kunden_id = ?");
        $stmt->execute([$customerId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Kunde nicht gefunden']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Methode nicht erlaubt']);
        break;
} 