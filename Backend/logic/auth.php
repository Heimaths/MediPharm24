<?php
session_start();

$response = [
    'logged_in' => false,
    'is_admin' => false
];

// Prüfe zuerst die Session
if (isset($_SESSION['user_id'])) {
    $response['logged_in'] = true;
    $response['is_admin'] = $_SESSION['is_admin'] ?? false;
} 
// Wenn keine Session, prüfe die Remember-Cookies
elseif (isset($_COOKIE['remember_data']) && isset($_COOKIE['remember_key'])) {
    $cookieData = base64_decode($_COOKIE['remember_data']);
    $key = $_COOKIE['remember_key'];
    
    // Extrahiere IV und verschlüsselte Daten
    $iv = substr($cookieData, 0, 16);
    $encrypted = substr($cookieData, 16);
    
    // Entschlüssele die Daten
    $decrypted = openssl_decrypt(
        $encrypted,
        'AES-256-CBC',
        $key,
        0,
        $iv
    );
    
    if ($decrypted !== false) {
        $data = json_decode($decrypted, true);
        
        // Prüfe, ob die Daten noch gültig sind
        if ($data && isset($data['expires']) && $data['expires'] > time()) {
            // Setze die Session
            $_SESSION['user_id'] = $data['user_id'];
            $_SESSION['username'] = $data['username'];
            $_SESSION['email'] = $data['email'];
            $_SESSION['is_admin'] = $data['is_admin'];
            
            $response['logged_in'] = true;
            $response['is_admin'] = $data['is_admin'];
            
            // Verlängere die Cookies
            $expires = time() + (30 * 24 * 60 * 60); // 30 Tage
            setcookie('remember_data', $_COOKIE['remember_data'], $expires, '/', '', true, true);
            setcookie('remember_key', $_COOKIE['remember_key'], $expires, '/', '', true, true);
        } else {
            // Ungültige oder abgelaufene Daten - Cookies löschen
            setcookie('remember_data', '', time() - 3600, '/', '', true, true);
            setcookie('remember_key', '', time() - 3600, '/', '', true, true);
        }
    } else {
        // Ungültige Daten - Cookies löschen
        setcookie('remember_data', '', time() - 3600, '/', '', true, true);
        setcookie('remember_key', '', time() - 3600, '/', '', true, true);
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>
