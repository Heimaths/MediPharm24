<?php
class User {
    private $conn;
    private $table = 'users';

    public $id;
    public $salutation;
    public $first_name;
    public $last_name;
    public $address;
    public $postal_code;
    public $city;
    public $email;
    public $username;
    public $password;
    public $payment_info;
    public $is_admin;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register() {
        $query = "INSERT INTO " . $this->table . " SET
            salutation=:salutation, first_name=:first_name, last_name=:last_name,
            address=:address, postal_code=:postal_code, city=:city, email=:email,
            username=:username, password=:password, payment_info=:payment_info, is_admin=:is_admin";

        $stmt = $this->conn->prepare($query);

        $this->password = password_hash($this->password, PASSWORD_BCRYPT);

        $stmt->bindParam(':salutation', $this->salutation);
        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':city', $this->city);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':payment_info', $this->payment_info);
        $stmt->bindParam(':is_admin', $this->is_admin);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function login() {
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $this->username);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($this->password, $user['password'])) {
            $this->id = $user['id'];
            $this->salutation = $user['salutation'];
            $this->first_name = $user['first_name'];
            $this->address = $user['address'];
            $this->postal_code = $user['postal_code'];
            $this->city = $user['city'];
            $this->email = $user['email'];
            $this->username = $user['username'];
            $this->payment_info = $user['payment_info'];
            $this->is_admin = $user['is_admin'];
            return true;
        }
        return false;
    }
}
?>