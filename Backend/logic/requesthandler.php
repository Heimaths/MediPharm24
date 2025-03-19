<?php
include "dataHandler.php";
$handler = new DataHandler();
$action = $_GET['action'] ?? '';

if ($action == "getProducts") {
    echo json_encode(["status" => "success", "data" => $handler->getProducts()]);
}
?>