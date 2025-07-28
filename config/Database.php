<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function __construct() {
        // Check if running on localhost
        if ($_SERVER['SERVER_NAME'] === 'localhost') {
            // Local settings
            $this->host = 'localhost';
            $this->db_name = 'computer_repair';
            $this->username = 'root';
            $this->password = '';
        } else {
            // Live/Production settings
            /*$this->host = 'localhost';
            $this->db_name = 'u402365599_comprep';
            $this->username = 'u402365599_devgauravmit';
            $this->password = '=rMe+Y:dx^6';*/

            //PWC
            $this->host = 'localhost';
            $this->db_name = 'u402365599_pwc';
            $this->username = 'u402365599_pwc';
            $this->password = 'S#u^d~I9';
            
        }
    }

    public function connect() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name}",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Connection error: " . $e->getMessage();
        }

        return $this->conn;
    }
}
?>
