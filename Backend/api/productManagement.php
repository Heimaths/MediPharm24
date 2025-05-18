<?php
session_start();
header('Content-Type: application/json');
require_once '../config/dbaccess.php';

// Überprüfe Admin-Berechtigung
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Keine Berechtigung']);
    exit();
}

$database = new Database();
$db = $database->connect();

// GET: Produkte abrufen
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $categoryId = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;
    $searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
    
    try {
        if ($categoryId && $searchTerm) {
            // Suche in bestimmter Kategorie
            $stmt = $db->prepare("SELECT p.*, k.name as kategorie_name 
                                FROM produkte p 
                                LEFT JOIN kategorien k ON p.kategorie_id = k.id 
                                WHERE p.kategorie_id = ? 
                                AND (p.name LIKE ? OR p.beschreibung LIKE ?)");
            $searchPattern = "%{$searchTerm}%";
            $stmt->execute([$categoryId, $searchPattern, $searchPattern]);
        } elseif ($categoryId) {
            // Nur Kategorie
            $stmt = $db->prepare("SELECT p.*, k.name as kategorie_name 
                                FROM produkte p 
                                LEFT JOIN kategorien k ON p.kategorie_id = k.id 
                                WHERE p.kategorie_id = ?");
            $stmt->execute([$categoryId]);
        } elseif ($searchTerm) {
            // Nur Suche
            $stmt = $db->prepare("SELECT p.*, k.name as kategorie_name 
                                FROM produkte p 
                                LEFT JOIN kategorien k ON p.kategorie_id = k.id 
                                WHERE p.name LIKE ? OR p.beschreibung LIKE ?");
            $searchPattern = "%{$searchTerm}%";
            $stmt->execute([$searchPattern, $searchPattern]);
        } else {
            // Alle Produkte
            $stmt = $db->query("SELECT p.*, k.name as kategorie_name 
                               FROM produkte p 
                               LEFT JOIN kategorien k ON p.kategorie_id = k.id");
        }
        
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'products' => $products]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Datenbankfehler: ' . $e->getMessage()]);
    }
}

// POST: Neues Produkt hinzufügen
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Ungültige Daten']);
        exit();
    }
    
    // Validierung der Pflichtfelder
    if (empty($data['name']) || empty($data['beschreibung']) || !isset($data['preis']) || !isset($data['kategorie_id'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Alle Pflichtfelder müssen ausgefüllt sein']);
        exit();
    }
    
    try {
        $stmt = $db->prepare("INSERT INTO produkte (name, beschreibung, preis, bild, kategorie_id) 
                             VALUES (?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $data['name'],
            $data['beschreibung'],
            $data['preis'],
            $data['bild'] ?? null,
            $data['kategorie_id']
        ]);
        
        $productId = $db->lastInsertId();
        echo json_encode([
            'status' => 'success', 
            'message' => 'Produkt erfolgreich hinzugefügt',
            'product_id' => $productId
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Datenbankfehler: ' . $e->getMessage()]);
    }
}

// PUT: Produkt aktualisieren
elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Ungültige Daten']);
        exit();
    }
    
    try {
        $stmt = $db->prepare("UPDATE produkte 
                             SET name = ?, beschreibung = ?, preis = ?, 
                                 bild = ?, kategorie_id = ? 
                             WHERE id = ?");
        
        $stmt->execute([
            $data['name'],
            $data['beschreibung'],
            $data['preis'],
            $data['bild'] ?? null,
            $data['kategorie_id'],
            $data['id']
        ]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Produkt erfolgreich aktualisiert']);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Produkt nicht gefunden']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Datenbankfehler: ' . $e->getMessage()]);
    }
}

// DELETE: Produkt löschen
elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $productId = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($productId <= 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Ungültige Produkt-ID']);
        exit();
    }
    
    try {
        // Prüfen, ob das Produkt in Bestellungen verwendet wird
        $stmt = $db->prepare("SELECT COUNT(*) FROM bestellpositionen WHERE produkt_id = ?");
        $stmt->execute([$productId]);
        if ($stmt->fetchColumn() > 0) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Produkt kann nicht gelöscht werden, da es in Bestellungen verwendet wird']);
            exit();
        }
        
        // Produkt löschen
        $stmt = $db->prepare("DELETE FROM produkte WHERE id = ?");
        $stmt->execute([$productId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Produkt erfolgreich gelöscht']);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Produkt nicht gefunden']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Datenbankfehler: ' . $e->getMessage()]);
    }
}

// Nicht unterstützte HTTP-Methode
else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Methode nicht erlaubt']);
}
?> 