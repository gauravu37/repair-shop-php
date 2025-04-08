<?php
class Item {
    private $conn;
    private $table = 'items';

    public $id;
    public $name;
    public $description;
    public $price;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = 'INSERT INTO ' . $this->table . ' 
            SET 
                name = :name,
                description = :description,
                price = :price';

        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->price = htmlspecialchars(strip_tags($this->price));

        // Bind data
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':price', $this->price);

        if($stmt->execute()) {
            return true;
        }

        printf("Error: %s.\n", $stmt->error);
        return false;
    }

    public function read() {
        $query = 'SELECT id, name, description, price, created_at 
                  FROM ' . $this->table . ' 
                  ORDER BY name';
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // Add other methods like update, delete, get by id, etc.
}
?>