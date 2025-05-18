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

// Überprüfe, ob eine Datei hochgeladen wurde
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Keine Datei hochgeladen oder Upload-Fehler']);
    exit();
}

$file = $_FILES['image'];
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
$maxFileSize = 5 * 1024 * 1024; // 5MB

// Überprüfe Dateityp und -größe
if (!in_array($file['type'], $allowedTypes)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Nur JPG, PNG und GIF Dateien sind erlaubt']);
    exit();
}

if ($file['size'] > $maxFileSize) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Datei ist zu groß (max. 5MB)']);
    exit();
}

// Erstelle Upload-Verzeichnis falls nicht vorhanden
$uploadDir = '../../Frontend/res/img/products/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Generiere eindeutigen Dateinamen
$fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
$fileName = uniqid('product_') . '.' . $fileExtension;
$targetPath = $uploadDir . $fileName;

// Verschiebe die Datei
if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    // Gebe den relativen Pfad zurück
    $relativePath = '/MediPharm24/Frontend/res/img/products/' . $fileName;
    echo json_encode([
        'status' => 'success',
        'message' => 'Bild erfolgreich hochgeladen',
        'image_path' => $relativePath
    ]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Fehler beim Speichern der Datei']);
}
?> 