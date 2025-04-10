<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;

try {
    if ($categoryId > 0) {
        $stmt = $pdo->prepare("
            SELECT p.id, p.name, p.price, p.image, 
                   COALESCE(AVG(r.rating), 0) as rating
            FROM products p
            LEFT JOIN reviews r ON p.id = r.product_id
            WHERE p.category_id = ?
            GROUP BY p.id
            ORDER BY p.name
        ");
        $stmt->execute([$categoryId]);
    } else {
        $stmt = $pdo->query("
            SELECT p.id, p.name, p.price, p.image, 
                   COALESCE(AVG(r.rating), 0) as rating
            FROM products p
            LEFT JOIN reviews r ON p.id = r.product_id
            GROUP BY p.id
            ORDER BY p.name
        ");
    }
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Bildpfade anpassen
    foreach ($products as &$product) {
        $product['image'] = '/MediPharm24/Backend/uploads/' . $product['image'];
    }
    
    echo json_encode($products);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Datenbankfehler: ' . $e->getMessage()]);
}
?> 