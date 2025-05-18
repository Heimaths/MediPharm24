<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Nicht eingeloggt']);
    exit;
}

if (!isset($_POST['code'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Kein Gutscheincode angegeben']);
    exit;
}

$code = trim($_POST['code']);

require_once '../config/dbaccess.php';
$database = new Database();
$pdo = $database->connect();

$stmt = $pdo->prepare("SELECT * FROM gutscheine WHERE code = ?");
$stmt->execute([$code]);
$coupon = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$coupon) {
    echo json_encode(['status' => 'error', 'message' => 'Gutschein nicht gefunden']);
    exit;
}

if ($coupon['eingeloest'] == 1) {
    echo json_encode(['status' => 'error', 'message' => 'Gutschein wurde bereits eingelöst']);
    exit;
}

if (!empty($coupon['gueltig_bis']) && strtotime($coupon['gueltig_bis']) < strtotime(date('Y-m-d'))) {
    echo json_encode(['status' => 'error', 'message' => 'Gutschein ist abgelaufen']);
    exit;
}

echo json_encode([
    'status' => 'success',
    'rabatt' => floatval($coupon['rabatt']),
    'code' => $coupon['code'],
    'gueltig_bis' => $coupon['gueltig_bis']
]);
exit;
?> 