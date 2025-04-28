<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/dbaccess.php';
$database = new Database();
$pdo = $database->connect();

session_start();

// Warenkorb in Session initialisieren, falls nicht vorhanden
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Warenkorb-Inhalt zurückgeben
    $cartItems = [];
    $total = 0;
    
    if (!empty($_SESSION['cart'])) {
        $productIds = array_keys($_SESSION['cart']);
        $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
        
        $stmt = $pdo->prepare("
            SELECT id, name, preis as price 
            FROM produkte 
            WHERE id IN ($placeholders)
        ");
        $stmt->execute($productIds);
        
        while ($product = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $quantity = $_SESSION['cart'][$product['id']];
            $cartItems[] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => $quantity,
                'subtotal' => $product['price'] * $quantity
            ];
            $total += $product['price'] * $quantity;
        }
    }
    
    echo json_encode([
        'items' => $cartItems,
        'total' => $total,
        'count' => array_sum($_SESSION['cart'])
    ]);
} 
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'Ungültige Daten']);
        exit;
    }
    
    $action = $data['action'] ?? '';
    $productId = (int)($data['productId'] ?? 0);
    
    if ($productId <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Ungültige Produkt-ID']);
        exit;
    }
    
    // Überprüfen, ob das Produkt existiert
    $stmt = $pdo->prepare("SELECT id FROM produkte WHERE id = ?");
    $stmt->execute([$productId]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Produkt nicht gefunden']);
        exit;
    }
    
    switch ($action) {
        case 'add':
            $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + 1;
            break;
            
        case 'remove':
            if (isset($_SESSION['cart'][$productId])) {
                unset($_SESSION['cart'][$productId]);
            }
            break;
            
        case 'update':
            $quantity = (int)($data['quantity'] ?? 0);
            if ($quantity > 0) {
                $_SESSION['cart'][$productId] = $quantity;
            } else {
                unset($_SESSION['cart'][$productId]);
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Ungültige Aktion']);
            exit;
    }
    
    // Aktualisierten Warenkorb zurückgeben
    $cartCount = array_sum($_SESSION['cart']);
    echo json_encode([
        'success' => true, 
        'count' => $cartCount,
        'cart' => $_SESSION['cart']
    ]);
}
?> 