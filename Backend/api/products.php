<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/dbaccess.php';
$database = new Database();
$pdo = $database->connect();

$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

try {
    if ($categoryId > 0) {
        if (!empty($searchTerm)) {
            $stmt = $pdo->prepare("
                SELECT p.id, p.name, p.preis, p.bild, 
                    ROUND(COALESCE(AVG(b.rating), 0), 2) as rating
                FROM produkte p
                LEFT JOIN bewertungen b ON p.id = b.produkt_id
                WHERE p.kategorie_id = ? AND p.name LIKE ?
                GROUP BY p.id
                ORDER BY p.name
            ");
            $stmt->execute([$categoryId, "%$searchTerm%"]);
        } else {
            $stmt = $pdo->prepare("
                SELECT p.id, p.name, p.preis, p.bild, 
                    ROUND(COALESCE(AVG(b.rating), 0), 2) as rating
                FROM produkte p
                LEFT JOIN bewertungen b ON p.id = b.produkt_id
                WHERE p.kategorie_id = ?
                GROUP BY p.id
                ORDER BY p.name
            ");
            $stmt->execute([$categoryId]);
        }
    } else {
        if (!empty($searchTerm)) {
            $stmt = $pdo->prepare("
                SELECT p.id, p.name, p.preis, p.bild, 
                    ROUND(COALESCE(AVG(b.rating), 0), 2) as rating
                FROM produkte p
                LEFT JOIN bewertungen b ON p.id = b.produkt_id
                WHERE p.name LIKE ?
                GROUP BY p.id
                ORDER BY p.name
            ");
            $stmt->execute(["%$searchTerm%"]);
        } else {
            $stmt = $pdo->query("
                SELECT p.id, p.name, p.preis, p.bild, 
                ROUND(COALESCE(AVG(b.rating), 0), 2) as rating
                FROM produkte p
                LEFT JOIN bewertungen b ON p.id = b.produkt_id
                GROUP BY p.id
                ORDER BY p.name
            ");
        }
    }
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Bildpfade anpassen
    foreach ($products as &$product) {
        $product['bild'] = '/MediPharm24/Backend/uploads/' . $product['bild'];
    }
    
    echo json_encode($products);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Datenbankfehler: ' . $e->getMessage()]);
}
?> 