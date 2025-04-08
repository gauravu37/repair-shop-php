<?php
class RepairCase {
    private $conn;
    private $table = 'cases';

    public $id;
    public $user_id;
    public $case_date;
    public $item_type;
    public $problem_description;
    public $estimated_delivery;
    public $estimated_price;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = 'INSERT INTO ' . $this->table . ' 
            SET 
                user_id = :user_id,
                item_type = :item_type,
                problem_description = :problem_description,
                estimated_delivery = :estimated_delivery,
                estimated_price = :estimated_price';

        $stmt = $this->conn->prepare($query);

        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->item_type = htmlspecialchars(strip_tags($this->item_type));
        $this->problem_description = htmlspecialchars(strip_tags($this->problem_description));
        $this->estimated_delivery = htmlspecialchars(strip_tags($this->estimated_delivery));
        $this->estimated_price = htmlspecialchars(strip_tags($this->estimated_price));

        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':item_type', $this->item_type);
        $stmt->bindParam(':problem_description', $this->problem_description);
        $stmt->bindParam(':estimated_delivery', $this->estimated_delivery);
        $stmt->bindParam(':estimated_price', $this->estimated_price);

        if($stmt->execute()) {
            return true;
        }

        printf("Error: %s.\n", $stmt->error);
        return false;
    }

    public function read() {
        $query = 'SELECT * FROM ' . $this->table . ' ORDER BY case_date DESC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>