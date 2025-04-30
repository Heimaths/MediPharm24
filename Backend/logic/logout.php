<?php
session_start();
include '../config/dbaccess.php';

$database = new Database();
$db = $database->connect();

// Wenn ein Remember-Token existiert, lösche es aus der Datenbank
if (isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    $stmt = $db->prepare("DELETE FROM remember_tokens WHERE token = ?");
    $stmt->execute([$token]);
    
    // Lösche das Cookie
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
}

// Session zerstören
session_destroy();

// Zurück zur Startseite
header('Location: /MediPharm24/Frontend/index.html');
exit();
?>

