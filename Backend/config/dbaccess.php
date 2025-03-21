<?php
class Database {
    private $host = "localhost";
    private $dbname = "webshop_db";
    private $username = "root";
    private $password = "";

    public function connect() {
        try {
            $conn = new PDO("mysql:host=".$this->host.";dbname=".$this->dbname, $this->username, $this->password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->exec("set names utf8");
            return $conn;
        } catch(PDOException $e) {
            die("Connection error: " . $e->getMessage());
        }
    }
}
?>
