<?php
class User {
    private $conn;
    private $table = 'kunden';  // Achte darauf, dass diese Tabelle existiert!

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
    public $is_active;

    public function __construct($db) {
        $this->conn = $db;
    }

    private function checkUsernameExists($username, $excludeId = null) {
        $query = "SELECT id FROM " . $this->table . " WHERE username = :username";
        if ($excludeId) {
            $query .= " AND id != :id";
        }
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        if ($excludeId) {
            $stmt->bindParam(':id', $excludeId);
        }
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    private function checkEmailExists($email, $excludeId = null) {
        $query = "SELECT id FROM " . $this->table . " WHERE email = :email";
        if ($excludeId) {
            $query .= " AND id != :id";
        }
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        if ($excludeId) {
            $stmt->bindParam(':id', $excludeId);
        }
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function register() {
        // Prüfe auf doppelte Benutzernamen und E-Mail-Adressen
        if ($this->checkUsernameExists($this->username)) {
            return ['success' => false, 'message' => 'Dieser Benutzername ist bereits vergeben.'];
        }
        if ($this->checkEmailExists($this->email)) {
            return ['success' => false, 'message' => 'Diese E-Mail-Adresse ist bereits registriert.'];
        }

        $query = "INSERT INTO " . $this->table . " SET
            salutation = :salutation, 
            first_name = :first_name, 
            last_name = :last_name,
            address = :address, 
            postal_code = :postal_code, 
            city = :city, 
            email = :email,
            username = :username, 
            password = :password, 
            payment_info = :payment_info, 
            is_admin = :is_admin,
            is_active = 1";  // Neue Benutzer sind standardmäßig aktiv

        $stmt = $this->conn->prepare($query);

        // Hash the password
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);

        $stmt->bindParam(':salutation', $this->salutation);
        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':postal_code', $this->postal_code);
        $stmt->bindParam(':city', $this->city);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':payment_info', $this->payment_info);
        $stmt->bindParam(':is_admin', $this->is_admin);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Registrierung erfolgreich'];
        }
        return ['success' => false, 'message' => 'Fehler bei der Registrierung'];
    }

    public function update() {
        // Prüfe auf doppelte Benutzernamen und E-Mail-Adressen, ausschließlich des aktuellen Benutzers
        if ($this->checkUsernameExists($this->username, $this->id)) {
            return ['success' => false, 'message' => 'Dieser Benutzername ist bereits vergeben.'];
        }
        if ($this->checkEmailExists($this->email, $this->id)) {
            return ['success' => false, 'message' => 'Diese E-Mail-Adresse ist bereits registriert.'];
        }

        $query = "UPDATE " . $this->table . " SET
            salutation = :salutation,
            first_name = :first_name,
            last_name = :last_name,
            address = :address,
            postal_code = :postal_code,
            city = :city,
            email = :email,
            is_admin = :is_admin,
            is_active = :is_active";

        // Füge Passwort-Update hinzu, wenn ein neues Passwort gesetzt wurde
        if (!empty($this->password)) {
            $query .= ", password = :password";
        }

        $query .= " WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':salutation', $this->salutation);
        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':postal_code', $this->postal_code);
        $stmt->bindParam(':city', $this->city);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':is_admin', $this->is_admin);
        $stmt->bindParam(':is_active', $this->is_active);
        $stmt->bindParam(':id', $this->id);

        if (!empty($this->password)) {
            $this->password = password_hash($this->password, PASSWORD_BCRYPT);
            $stmt->bindParam(':password', $this->password);
        }

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Benutzer erfolgreich aktualisiert'];
        }
        return ['success' => false, 'message' => 'Fehler beim Aktualisieren des Benutzers'];
    }

    public function login() {
        // SQL-Query: Prüfe, ob die Eingabe eine E-Mail-Adresse oder ein Benutzername ist
        $query = "SELECT * FROM " . $this->table . " WHERE username = :identifier OR email = :identifier LIMIT 1";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':identifier', $this->username); // Hier kann username oder email stehen
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($this->password, $user['password'])) {
            // Prüfe, ob der Benutzer aktiv ist
            if (!$user['is_active']) {
                return ['success' => false, 'message' => 'Ihr Konto wurde deaktiviert. Bitte kontaktieren Sie den Support.'];
            }
            
            // Benutzerdaten setzen
            $this->id = $user['id'];
            $this->salutation = $user['salutation'];
            $this->first_name = $user['first_name'];
            $this->last_name = $user['last_name'];
            $this->address = $user['address'];
            $this->postal_code = $user['postal_code'];
            $this->city = $user['city'];
            $this->email = $user['email'];
            $this->username = $user['username'];
            $this->payment_info = $user['payment_info'];
            $this->is_admin = $user['is_admin'];
            $this->is_active = $user['is_active'];
            
            return ['success' => true];
        }
        return ['success' => false, 'message' => 'Ungültige Anmeldedaten'];
    }
}
?>
