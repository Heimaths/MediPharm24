<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

function login($email, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT kunden_id, vorname, nachname, email, passwort, is_admin, is_active 
        FROM kunden 
        WHERE email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['passwort'])) {
        if (!$user['is_active']) {
            return ['success' => false, 'message' => 'Ihr Konto wurde deaktiviert'];
        }
        
        $_SESSION['user_id'] = $user['kunden_id'];
        $_SESSION['vorname'] = $user['vorname'];
        $_SESSION['nachname'] = $user['nachname'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['is_admin'] = $user['is_admin'];
        
        return ['success' => true];
    }
    
    return ['success' => false, 'message' => 'Ungültige E-Mail oder Passwort'];
}

function logout() {
    session_destroy();
    header('Location: /login.php');
    exit;
}

// Überprüfen ob Benutzer eingeloggt ist
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /login.php');
        exit;
    }
}

// Überprüfen ob Benutzer Admin ist
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: /');
        exit;
    }
} 