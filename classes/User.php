<?php
class User {
    private $conn;
    private $table = 'users';

    public $id;
    public $username;
    public $email;
    public $phone;
    public $whatsapp;
    public $address;
    public $user_type;
    public $password;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = 'INSERT INTO ' . $this->table . ' 
            SET 
                username = :username,
                email = :email,
                phone = :phone,
                whatsapp = :whatsapp,
                address = :address,
                user_type = :user_type,
                password = :password';

        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->whatsapp = htmlspecialchars(strip_tags($this->whatsapp));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->user_type = htmlspecialchars(strip_tags($this->user_type));
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);

        // Bind data
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':whatsapp', $this->whatsapp);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':user_type', $this->user_type);
        $stmt->bindParam(':password', $this->password);

        if($stmt->execute()) {
            return true;
        }

        printf("Error: %s.\n", $stmt->error);
        return false;
    }

    public function read() {
        $query = 'SELECT id, username, email, phone, whatsapp, address, user_type, created_at 
                  FROM ' . $this->table . ' 
                  ORDER BY created_at DESC';
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // Add other methods like update, delete, get by id, etc.
}
?>