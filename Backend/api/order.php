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

$input = json_decode(file_get_contents('php://input'), true);
$couponCode = isset($input['coupon']) ? trim($input['coupon']) : null;
$gutschein_id = null;

if ($couponCode) {
    // Gutschein prüfen und ID holen
    $stmt = $db->prepare("SELECT id, eingeloest, gueltig_bis FROM gutscheine WHERE code = ?");
    $stmt->execute([$couponCode]);
    $gutschein = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$gutschein) {
        echo json_encode(['status' => 'error', 'message' => 'Gutschein ungültig']);
        exit();
    }
    if ($gutschein['eingeloest'] == 1) {
        echo json_encode(['status' => 'error', 'message' => 'Gutschein wurde bereits eingelöst']);
        exit();
    }
    if (!empty($gutschein['gueltig_bis']) && strtotime($gutschein['gueltig_bis']) < strtotime(date('Y-m-d'))) {
        echo json_encode(['status' => 'error', 'message' => 'Gutschein ist abgelaufen']);
        exit();
    }
    $gutschein_id = $gutschein['id'];
}

try {
    // 1. Bestellung anlegen
    $stmt = $db->prepare("INSERT INTO bestellungen (kunde_id, status, gutschein_id) VALUES (?, ?, ?)");
    $stmt->execute([$kunde_id, $status, $gutschein_id]);
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

    // Gutschein als eingelöst markieren
    if ($gutschein_id) {
        $stmt = $db->prepare("UPDATE gutscheine SET eingeloest = 1 WHERE id = ?");
        $stmt->execute([$gutschein_id]);
    }

    echo json_encode(['status' => 'success', 'order_id' => $order_id]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Fehler beim Anlegen der Bestellung: ' . $e->getMessage()]);
} 