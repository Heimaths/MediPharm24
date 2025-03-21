<?php
 $servername = "localhost";
 $dbname = "webshop_db";
 $username = "root";
 $password = "";
 $conn;

// Verbindung herstellen
$conn = new mysqli($servername, $username, $password, $dbname);

// Verbindung überprüfen
if ($conn->connect_error) {
 die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}
?>