<?php
// Stelle sicher, dass du die PDO-basierte Database-Klasse nutzt
require '../config/dbaccess.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Eingaben trimmen und validieren
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);

    // Serverseitige Validierung
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

    // password hashen
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Datenbankverbindung aufbauen (PDO)
    $database = new Database();
    $conn = $database->connect();

    // SQL-Query vorbereiten und Parameter binden
    $query = "INSERT INTO kunden (username, email, password) VALUES (:username, :email, :password)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $hashedPassword);

    // Query ausführen und Rückmeldung geben
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Registrierung erfolgreich.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Fehler bei der Registrierung.']);
    }
    exit();
}
?>
