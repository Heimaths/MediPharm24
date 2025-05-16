<?php
session_start();
require_once '../config/dbaccess.php';

// Fehlerberichterstattung aktivieren
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    die('Nicht eingeloggt');
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
if ($order_id <= 0) {
    header('HTTP/1.1 400 Bad Request');
    die('Ungültige Bestellnummer');
}

$database = new Database();
$db = $database->connect();

try {
    // Überprüfen, ob die Bestellung dem Benutzer gehört oder ob der User Admin ist
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
        // Admin: Bestellung unabhängig vom Kunden laden
        $stmt = $db->prepare("SELECT * FROM bestellungen WHERE id = ?");
        $stmt->execute([$order_id]);
    } else {
        // Normaler User: Nur eigene Bestellung
        $stmt = $db->prepare("SELECT * FROM bestellungen WHERE id = ? AND kunde_id = ?");
        $stmt->execute([$order_id, $_SESSION['user_id']]);
    }
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        header('HTTP/1.1 404 Not Found');
        die('Bestellung nicht gefunden');
    }

    // Rechnungsnummer generieren
    $invoice_number = 'R-' . date('Y') . '-' . str_pad($order_id, 6, '0', STR_PAD_LEFT);

    // Bestellpositionen und Benutzerdaten holen
    $stmt = $db->prepare("
        SELECT bp.*, p.name, p.preis
        FROM bestellpositionen bp
        JOIN produkte p ON bp.produkt_id = p.id
        WHERE bp.bestellung_id = ?
    ");
    $stmt->execute([$order_id]);
    $positions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Rechnungsadresse laden
    $kunde_id = $order['kunde_id'];
    $stmt = $db->prepare("
        SELECT salutation, first_name, last_name, address, postal_code, city
        FROM kunden
        WHERE id = ?
    ");
    $stmt->execute([$kunde_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // HTML für die Rechnung generieren
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Rechnung ' . $invoice_number . '</title>
        <style>
            body { 
                font-family: Arial, sans-serif;
                margin: 20px;
                padding: 20px;
            }
            .header { 
                text-align: right;
                margin-bottom: 30px;
            }
            .address { 
                margin-bottom: 30px;
            }
            table { 
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 30px;
            }
            th, td { 
                border: 1px solid #000;
                padding: 8px;
                text-align: left;
            }
            th { 
                background-color: #f2f2f2;
            }
            .total { 
                text-align: right;
                font-weight: bold;
            }
            .print-button {
                display: block;
                margin: 20px auto;
                padding: 10px 20px;
                background-color: #4CAF50;
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
            }
            @media print {
                .print-button {
                    display: none;
                }
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Rechnung</h1>
            <p>Rechnungsnummer: ' . $invoice_number . '</p>
            <p>Datum: ' . date('d.m.Y') . '</p>
        </div>

        <div class="address">
            <h2>Rechnungsadresse:</h2>
            <p>' . htmlspecialchars(($user['salutation'] ?? '') . ' ' . ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) . '</p>
            <p>' . htmlspecialchars($user['address'] ?? '') . '</p>
            <p>' . htmlspecialchars(($user['postal_code'] ?? '') . ' ' . ($user['city'] ?? '')) . '</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Produkt</th>
                    <th>Menge</th>
                    <th>Einzelpreis</th>
                    <th>Gesamt</th>
                </tr>
            </thead>
            <tbody>';

    $total = 0;
    foreach ($positions as $position) {
        $row_total = $position['menge'] * $position['preis'];
        $total += $row_total;
        $html .= '
                <tr>
                    <td>' . htmlspecialchars($position['name']) . '</td>
                    <td>' . $position['menge'] . '</td>
                    <td>' . number_format($position['preis'], 2) . ' €</td>
                    <td>' . number_format($row_total, 2) . ' €</td>
                </tr>';
    }

    $html .= '
            </tbody>
        </table>

        <div class="total">
            <p>Gesamtbetrag: ' . number_format($total, 2) . ' €</p>
        </div>

        <button class="print-button" onclick="window.print()">Rechnung drucken</button>
    </body>
    </html>';

    // HTML ausgeben
    header('Content-Type: text/html; charset=utf-8');
    echo $html;
    exit;

} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    error_log('PDF-Generierungsfehler: ' . $e->getMessage());
    die('Fehler bei der PDF-Generierung. Bitte versuchen Sie es später erneut.');
}
?> 