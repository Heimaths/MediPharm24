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

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Bestellungs-ID fehlt']);
    exit;
}

$orderId = (int)$_GET['id'];

try {
    $database = new Database();
    $pdo = $database->connect();

    // Bestelldetails abrufen
    $stmt = $pdo->prepare("
        SELECT o.*, k.first_name, k.last_name, k.email, g.code as gutschein_code, g.rabatt as gutschein_rabatt
        FROM bestellungen o
        JOIN kunden k ON o.kunde_id = k.id
        LEFT JOIN gutscheine g ON o.gutschein_id = g.id
        WHERE o.id = ?
    ");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Bestellung nicht gefunden']);
        exit;
    }

    // Bestellte Artikel abrufen
    $stmt = $pdo->prepare("
        SELECT oi.*, p.name as product_name
        FROM bestellpositionen oi
        JOIN produkte p ON oi.produkt_id = p.id
        WHERE oi.bestellung_id = ?
    ");
    $stmt->execute([$orderId]);
    $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'order' => $order
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Datenbankfehler: ' . $e->getMessage()
    ]);
}
?> 