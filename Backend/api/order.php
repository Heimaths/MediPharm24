<?php
session_start();
header('Content-Type: application/json');
require_once '../config/dbaccess.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Nicht eingeloggt']);
    exit();
}

if (empty($_SESSION['cart'])) {
    echo json_encode(['status' => 'error', 'message' => 'Warenkorb ist leer']);
    exit();
}

$database = new Database();
$db = $database->connect();

$kunde_id = $_SESSION['user_id'];
$status = 'offen';

try {
    // 1. Bestellung anlegen
    $stmt = $db->prepare("INSERT INTO bestellungen (kunde_id, status) VALUES (?, ?)");
    $stmt->execute([$kunde_id, $status]);
    $order_id = $db->lastInsertId();

    // 2. Produkte aus dem Warenkorb holen
    $cart = $_SESSION['cart'];
    $productIds = array_keys($cart);

    if (count($productIds) > 0) {
        // Produktpreise holen
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $stmt = $db->prepare("SELECT id, preis FROM produkte WHERE id IN ($placeholders)");
        $stmt->execute($productIds);
        $preise = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $preise[$row['id']] = $row['preis'];
        }

        // 3. Bestellpositionen anlegen
        $stmt = $db->prepare("INSERT INTO bestellpositionen (bestellung_id, produkt_id, menge, einzelpreis) VALUES (?, ?, ?, ?)");
        foreach ($cart as $produkt_id => $menge) {
            $einzelpreis = isset($preise[$produkt_id]) ? $preise[$produkt_id] : 0;
            $ok = $stmt->execute([$order_id, $produkt_id, $menge, $einzelpreis]);
            if (!$ok) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Fehler beim Anlegen der Bestellposition: ' . implode(' | ', $stmt->errorInfo())
                ]);
                exit();
            }
        }
    }

    // 4. Warenkorb leeren
    $_SESSION['cart'] = [];

    echo json_encode(['status' => 'success', 'order_id' => $order_id]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Fehler beim Anlegen der Bestellung: ' . $e->getMessage()]);
} 