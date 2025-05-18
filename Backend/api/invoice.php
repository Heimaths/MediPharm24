<?php
session_start();
header('Content-Type: application/json');
require_once '../config/dbaccess.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Nicht eingeloggt']);
    exit();
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
if ($order_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Ungültige Bestellnummer']);
    exit();
}

$database = new Database();
$db = $database->connect();

try {
    // Überprüfen, ob die Bestellung dem Benutzer gehört
    $stmt = $db->prepare("SELECT * FROM bestellungen WHERE id = ? AND kunde_id = ?");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo json_encode(['status' => 'error', 'message' => 'Bestellung nicht gefunden']);
        exit();
    }

    // Rechnungsnummer dynamisch generieren (Beispiel: R-2024-000001)
    $invoice_number = 'R-' . date('Y') . '-' . str_pad($order_id, 6, '0', STR_PAD_LEFT);

    // Bestellpositionen und Benutzerdaten holen
    $stmt = $db->prepare("
        SELECT bp.*, p.name, p.preis, g.code as gutschein_code, g.rabatt as gutschein_rabatt
        FROM bestellungen b
        LEFT JOIN gutscheine g ON b.gutschein_id = g.id
        JOIN bestellpositionen bp ON b.id = bp.bestellung_id
        JOIN produkte p ON bp.produkt_id = p.id
        WHERE b.id = ?
    ");
    $stmt->execute([$order_id]);
    $positions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $db->prepare("
        SELECT salutation, first_name, last_name, address, postal_code, city
        FROM kunden
        WHERE id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Gesamtbetrag berechnen
    $total = 0;
    foreach ($positions as $position) {
        $total += $position['menge'] * $position['preis'];
    }

    // Gutschein-Rabatt anwenden, falls vorhanden
    $gutschein_code = $positions[0]['gutschein_code'] ?? null;
    $gutschein_rabatt = $positions[0]['gutschein_rabatt'] ?? 0;
    if ($gutschein_code) {
        $gutschein_rabatt = min($gutschein_rabatt, $total);
        $total -= $gutschein_rabatt;
    }

    // Rechnungsdaten zusammenstellen
    $invoice_data = [
        'rechnungsnummer' => $invoice_number,
        'datum' => date('d.m.Y'),
        'kunde' => $user,
        'positionen' => $positions,
        'gutschein' => $gutschein_code ? [
            'code' => $gutschein_code,
            'rabatt' => $gutschein_rabatt
        ] : null,
        'gesamtbetrag' => $total
    ];

    echo json_encode(['status' => 'success', 'invoice' => $invoice_data]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Fehler bei der Rechnungsgenerierung: ' . $e->getMessage()]);
}
?> 