<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

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
            SELECT id, name, price 
            FROM products 
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
    $action = $data['action'] ?? '';
    $productId = (int)($data['productId'] ?? 0);
    
    if ($productId <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Ungültige Produkt-ID']);
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
    
    echo json_encode(['success' => true, 'count' => array_sum($_SESSION['cart'])]);
}
?> 