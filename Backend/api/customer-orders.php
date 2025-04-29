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

$customerId = $_GET['customer_id'] ?? null;

if (!$customerId) {
    http_response_code(400);
    echo json_encode(['error' => 'Kunden-ID erforderlich']);
    exit;
}

// Bestellungen des Kunden abrufen
$stmt = $pdo->prepare("
    SELECT 
        b.bestellungs_id,
        b.datum,
        p.name as produkt_name,
        bd.menge,
        bd.preis,
        bd.is_visible,
        bd.bestellungsdetails_id
    FROM bestellungen b
    JOIN bestellungsdetails bd ON b.bestellungs_id = bd.bestellungs_id
    JOIN produkte p ON bd.produkt_id = p.produkt_id
    WHERE b.kunden_id = ?
    ORDER BY b.datum DESC
");

$stmt->execute([$customerId]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($orders); 