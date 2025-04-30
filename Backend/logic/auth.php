<?php
session_start();
include '../config/dbaccess.php';

$database = new Database();
$db = $database->connect();

$response = [
    'logged_in' => false,
    'is_admin' => false
];

// Prüfe zuerst die Session
if (isset($_SESSION['user_id'])) {
    $response['logged_in'] = true;
    $response['is_admin'] = $_SESSION['is_admin'] ?? false;
} 
// Wenn keine Session, prüfe das Remember-Token Cookie
elseif (isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    
    // Hole den Token aus der Datenbank
    $stmt = $db->prepare("
        SELECT u.id, u.username, u.email, u.is_admin 
        FROM remember_tokens rt 
        JOIN kunden u ON rt.user_id = u.id 
        WHERE rt.token = ? AND rt.expires > NOW()
    ");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Setze die Session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['is_admin'] = (bool) $user['is_admin'];
        
        $response['logged_in'] = true;
        $response['is_admin'] = (bool) $user['is_admin'];
        
        // Verlängere das Cookie
        $expires = time() + (30 * 24 * 60 * 60); // 30 Tage
        setcookie('remember_token', $token, $expires, '/', '', true, true);
        
        // Aktualisiere das Ablaufdatum in der Datenbank
        $stmt = $db->prepare("UPDATE remember_tokens SET expires = ? WHERE token = ?");
        $stmt->execute([date('Y-m-d H:i:s', $expires), $token]);
    } else {
        // Ungültiges oder abgelaufenes Token - Cookie löschen
        setcookie('remember_token', '', time() - 3600, '/', '', true, true);
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>
