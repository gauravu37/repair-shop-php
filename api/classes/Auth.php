<?php
class Auth {
    private $conn;
    private $table = 'users';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($email, $password) {
        // Sanitize input
        $email = htmlspecialchars(strip_tags($email));
        
        $query = "SELECT id, username, email, password_hash, user_type FROM " . $this->table . " 
                 WHERE email = '$email' LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        //$stmt->bindParam(':email', $email);
        $stmt->execute();
        //return $stmt; exit;
		
        if($stmt->rowCount() > 0) {
			
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            //return $row['password_hash']; exit;
            // Verify password
            if(password_verify($password, $row['password_hash'])) {
                // Password is correct, generate token
				
                $token = $this->generateToken($row['id']);
                
                return array(
                    "status" => "success",
                    "token" => $token,
                    "user" => array(
                        "id" => $row['id'],
                        "username" => $row['username'],
                        "email" => $row['email'],
                        "user_type" => $row['user_type']
                    )
                );
            }
        }
        
        return array("status" => "error", "message" => "Invalid credentials");
    }

    private function generateToken($user_id) {
        $token = bin2hex(random_bytes(32));
        $expiry = date("Y-m-d H:i:s", strtotime('+1 day'));
        
        $query = "UPDATE " . $this->table . " 
                 SET token = :token, token_expiry = :expiry 
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':expiry', $expiry);
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        
        return $token;
    }

    public function validateToken($token) {
        $query = "SELECT id, username, email, user_type, token_expiry 
                 FROM " . $this->table . " 
                 WHERE token = :token LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Check token expiry
            if(strtotime($row['token_expiry']) > time()) {
                return $row; // Valid token
            }
        }
        
        return false;
    }
}
?>