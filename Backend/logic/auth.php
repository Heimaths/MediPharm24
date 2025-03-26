<?php
session_start();

header('Content-Type: application/json');

$response = [
    'logged_in' => false,
    'is_admin' => false
];

if (isset($_SESSION['user_id'])) {
    $response['logged_in'] = true;
    $response['is_admin'] = $_SESSION['is_admin'] ?? false;
}

echo json_encode($response);
?>
