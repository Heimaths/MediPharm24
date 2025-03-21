<?php
   
require('../config/dbaccess.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);

    // Server-side validation
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        echo json_encode(['status' => 'error', 'message' => 'Bitte füllen Sie alle Felder aus.']);
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Ungültige E-Mail-Adresse.']);
        exit();
    }

    if ($password !== $confirmPassword) {
        echo json_encode(['status' => 'error', 'message' => 'Passwörter stimmen nicht überein.']);
        exit();
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insert the new user into the database
    $stmt = $conn->prepare('INSERT INTO kunden (username, email, passwort) VALUES (?, ?, ?)');
    $stmt->bind_param('sss', $username, $email, $hashedPassword);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Registrierung erfolgreich.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Fehler bei der Registrierung.']);
    }

    // Verbindungen schließen
    $stmt->close();
    $conn->close();
    exit(); // Exit kommt jetzt am richtigen Platz
}
?>
