<?php
class Database {
    private $host = "localhost";
    private $dbname = "webshop_db";
    private $username = "root";
    private $password = "";
    private $conn;

    public function connect() {
        $this->conn = new PDO("mysql:host=$this->host;dbname=$this->dbname", $this->username, $this->password);
        return $this->conn;
    }
}
?>