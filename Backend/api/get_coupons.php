<?php

// api/get_coupons.php
header('Content-Type: application/json');
session_start();

require_once '../config/dbaccess.php';
$db = (new Database())->connect();

// Alle Gutscheine auslesen
$stmt = $db->query("SELECT id, code, rabatt, gueltig_bis, COALESCE(eingeloest, 0) AS eingeloest FROM gutscheine ORDER BY gueltig_bis DESC, id DESC");
$coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($coupons);