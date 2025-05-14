<?php

// api/create_coupon.php
header('Content-Type: application/json');
session_start();

require_once '../config/dbaccess.php';
$db = (new Database())->connect();

// Eingaben validieren
$rabatt = isset($_POST['rabatt']) ? floatval($_POST['rabatt']) : null;
$gueltig_bis = isset($_POST['gueltig_bis']) ? $_POST['gueltig_bis'] : null;
if (!$rabatt || !$gueltig_bis) {
    echo json_encode(['success' => false, 'error' => 'Ungültige Daten']);
    exit;
}

// 5-stelliger alphanumerischer Code
$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
$code = '';
for ($i = 0; $i < 5; $i++) $code .= $chars[random_int(0, strlen($chars)-1)];

try {
    $stmt = $db->prepare("INSERT INTO gutscheine (code, rabatt, gueltig_bis) VALUES (?, ?, ?)");
    $stmt->execute([$code, $rabatt, $gueltig_bis]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' =>$e->getMessage()]);
}