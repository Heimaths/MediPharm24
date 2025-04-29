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
    case 'PUT':
        // Bestellposition ausblenden
        $data = json_decode(file_get_contents('php://input'), true);
        $orderItemId = $data['order_item_id'] ?? null;
        
        if (!$orderItemId) {
            http_response_code(400);
            echo json_encode(['error' => 'Bestellpositions-ID erforderlich']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE bestellungsdetails SET is_visible = FALSE WHERE bestellungsdetails_id = ?");
        $stmt->execute([$orderItemId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Bestellposition nicht gefunden']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Methode nicht erlaubt']);
        break;
} 