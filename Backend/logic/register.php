<?php
// register.php
include 'dbaccess.php'; // Include database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);

    // Server-side validation
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        die('Bitte füllen Sie alle Felder aus.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die('Ungültige E-Mail-Adresse.');
    }

    if ($password !== $confirmPassword) {
        die('Passwörter stimmen nicht überein.');
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insert the new user into the database
    $stmt = $conn->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)');
    $stmt->bind_param('sss', $username, $email, $hashedPassword);

    if ($stmt->execute()) {
        echo 'Registrierung erfolgreich.';
    } else {
        echo 'Fehler bei der Registrierung.';
    }

    $stmt->close();
    $conn->close();
}
?>