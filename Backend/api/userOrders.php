<?php
session_start();
header('Content-Type: application/json');
require_once '../config/dbaccess.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Nicht eingeloggt']);
    exit();
}

$database = new Database();
$db = $database->connect();
$kunde_id = $_SESSION['user_id'];

// Alle Bestellungen des Nutzers holen
$stmt = $db->prepare("SELECT * FROM bestellungen WHERE kunde_id = ? ORDER BY datum DESC");
$stmt->execute([$kunde_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($orders as &$order) {
    // Produkte zu dieser Bestellung holen
    $stmt2 = $db->prepare("
        SELECT bp.*, p.name 
        FROM bestellpositionen bp
        JOIN produkte p ON bp.produkt_id = p.id
        WHERE bp.bestellung_id = ?
    ");
    $stmt2->execute([$order['id']]);
    $order['produkte'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
}

echo json_encode(['status' => 'success', 'orders' => $orders]); 