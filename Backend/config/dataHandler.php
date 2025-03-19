<?php
include "dbaccess.php";
class DataHandler {
    private $db;
    public function __construct() {
        $this->db = (new Database())->connect();
    }
    public function getProducts() {
        $query = "SELECT * FROM produkte";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>