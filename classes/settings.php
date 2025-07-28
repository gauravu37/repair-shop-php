<?php
class Setting {
    private $conn;
    private $table = 'settings';

    public $id;
    public $business_name;
    public $contact_number;
    public $logo_path;
    public $qr_code_path;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table . " LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET 
                      business_name = :business_name,
                      contact_number = :contact_number,
                      logo_path = :logo_path,
                      qr_code_path = :qr_code_path
                  WHERE id = 1";

        $stmt = $this->conn->prepare($query);

        $this->business_name = htmlspecialchars(strip_tags($this->business_name));
        $this->contact_number = htmlspecialchars(strip_tags($this->contact_number));
        $this->logo_path = htmlspecialchars(strip_tags($this->logo_path));
        $this->qr_code_path = htmlspecialchars(strip_tags($this->qr_code_path));

        $stmt->bindParam(':business_name', $this->business_name);
        $stmt->bindParam(':contact_number', $this->contact_number);
        $stmt->bindParam(':logo_path', $this->logo_path);
        $stmt->bindParam(':qr_code_path', $this->qr_code_path);

        if($stmt->execute()) {
            return true;
        }

        printf("Error: %s.\n", $stmt->error);
        return false;
    }
}