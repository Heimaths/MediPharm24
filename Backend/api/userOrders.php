<?php
session_start();
header('Content-Type: application/json');

if (isset($_GET['user_id'])) {
    // Admin-Modus: Prüfe Adminrechte
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Keine Berechtigung']);
        exit;
    }
    $userId = (int)$_GET['user_id'];
} else {
    // User-Modus: Nur eigene Bestellungen
    if (!isset($_SESSION['user_id'])) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Nicht eingeloggt']);
        exit;
    }
    $userId = (int)$_SESSION['user_id'];
}

require_once '../config/dbaccess.php';

try {
    $database = new Database();
    $pdo = $database->connect();

    // Bestellungen des Benutzers abrufen
    $stmt = $pdo->prepare("
        SELECT o.id, o.datum, o.status, o.gutschein_id, g.code as gutschein_code, g.rabatt as gutschein_rabatt
        FROM bestellungen o
        LEFT JOIN gutscheine g ON o.gutschein_id = g.id
        WHERE o.kunde_id = ?
        ORDER BY o.datum DESC
    ");
    $stmt->execute([$userId]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Für jede Bestellung die Produkte abrufen
    foreach ($orders as &$order) {
        $stmt2 = $pdo->prepare("
            SELECT p.name, bp.menge, bp.einzelpreis
            FROM bestellpositionen bp
            JOIN produkte p ON bp.produkt_id = p.id
            WHERE bp.bestellung_id = ?
        ");
        $stmt2->execute([$order['id']]);
        $order['produkte'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode([
        'status' => 'success',
        'orders' => $orders
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Datenbankfehler: ' . $e->getMessage()
    ]);
}
?> 