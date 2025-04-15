<?php
require 'vendor/autoload.php';


class Job {
    private $conn;
    private $table = 'jobs';

    public $id;
    public $user_id;
    public $job_date;
    public $item_type;
    public $problem_description;
    public $estimated_delivery;
    public $estimated_price;
    public $status;
    public $devices;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        try {
            $this->conn->beginTransaction();
            
            // Insert main job
            $jobQuery = 'INSERT INTO ' . $this->table . ' 
                        (user_id, item_type, problem_description, estimated_delivery, estimated_price, status) 
                        VALUES (:user_id, :item_type, :problem_description, :estimated_delivery, :estimated_price, :status)';
            $jobStmt = $this->conn->prepare($jobQuery);
            $jobStmt->execute([
                ':user_id' => $this->user_id,
                ':item_type' => $this->item_type,
                ':problem_description' => $this->problem_description,
                ':estimated_delivery' => $this->estimated_delivery,
                ':estimated_price' => $this->estimated_price,
                ':status' => $this->status ?? 'pending'
            ]);
            
            $jobId = $this->conn->lastInsertId();
            
            // Insert devices
            if (!empty($this->devices)) {
                $devices = is_object($this->devices) ? json_decode(json_encode($this->devices), true) : $this->devices;
                
                foreach ($devices as $device) {
                    $deviceType = is_array($device) ? $device['device_type'] : $device->device_type;
                    $problemDesc = is_array($device) ? $device['problem_description'] : $device->problem_description;
                    $estimatedPrice = is_array($device) ? $device['estimated_price'] : $device->estimated_price;
                    
                    $deviceQuery = 'INSERT INTO job_devices
                                  (job_id, device_type, problem_description, estimated_price)
                                  VALUES (:job_id, :device_type, :problem_description, :estimated_price)';
                    $deviceStmt = $this->conn->prepare($deviceQuery);
                    $deviceStmt->execute([
                        ':job_id' => $jobId,
                        ':device_type' => $deviceType,
                        ':problem_description' => $problemDesc,
                        ':estimated_price' => $estimatedPrice
                    ]);
                }
            }
            
            // Send WhatsApp notification
            $this->sendMakeWebhookNotification($jobId);
            
            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error creating job: " . $e->getMessage());
            return false;
        }
    }

    public function read() {
        $query = 'SELECT j.*, u.full_name 
                  FROM ' . $this->table . ' j
                  LEFT JOIN users u ON j.user_id = u.id
                  ORDER BY j.job_date DESC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readSingle() {
        $query = 'SELECT j.*, u.full_name 
                 FROM ' . $this->table . ' j
                 LEFT JOIN users u ON j.user_id = u.id
                 WHERE j.id = ? LIMIT 1';
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->user_id = $row['user_id'];
            $this->item_type = $row['item_type'];
            $this->problem_description = $row['problem_description'];
            $this->estimated_delivery = $row['estimated_delivery'];
            $this->estimated_price = $row['estimated_price'];
            $this->status = $row['status'];
            $this->job_date = $row['job_date'];
            
            // Load devices
            $this->loadDevices();
            return true;
        }
        
        return false;
    }

    private function loadDevices() {
        $query = 'SELECT * FROM job_devices WHERE job_id = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $this->devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update() {
        try {
            $this->conn->beginTransaction();
            
            $query = 'UPDATE ' . $this->table . '
                     SET item_type = :item_type,
                         problem_description = :problem_description,
                         estimated_delivery = :estimated_delivery,
                         estimated_price = :estimated_price,
                         status = :status
                     WHERE id = :id';
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':item_type', $this->item_type);
            $stmt->bindParam(':problem_description', $this->problem_description);
            $stmt->bindParam(':estimated_delivery', $this->estimated_delivery);
            $stmt->bindParam(':estimated_price', $this->estimated_price);
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':id', $this->id);
            
            $stmt->execute();
            
            // Update devices
            if (!empty($this->devices)) {
                // First delete existing devices
                $deleteQuery = 'DELETE FROM job_devices WHERE job_id = ?';
                $deleteStmt = $this->conn->prepare($deleteQuery);
                $deleteStmt->bindParam(1, $this->id);
                $deleteStmt->execute();
                
                // Insert new devices
                $devices = is_object($this->devices) ? json_decode(json_encode($this->devices), true) : $this->devices;
                
                foreach ($devices as $device) {
                    $deviceType = is_array($device) ? $device['device_type'] : $device->device_type;
                    $problemDesc = is_array($device) ? $device['problem_description'] : $device->problem_description;
                    $estimatedPrice = is_array($device) ? $device['estimated_price'] : $device->estimated_price;
                    
                    $deviceQuery = 'INSERT INTO job_devices
                                  (job_id, device_type, problem_description, estimated_price)
                                  VALUES (:job_id, :device_type, :problem_description, :estimated_price)';
                    $deviceStmt = $this->conn->prepare($deviceQuery);
                    $deviceStmt->execute([
                        ':job_id' => $this->id,
                        ':device_type' => $deviceType,
                        ':problem_description' => $problemDesc,
                        ':estimated_price' => $estimatedPrice
                    ]);
                }
            }
            
            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error updating job: " . $e->getMessage());
            return false;
        }
    }

    private function getCustomerPhone($userId) {
        $query = 'SELECT phone FROM users WHERE id = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['phone'] ?? null;
    }

    private function sendMakeWebhookNotification($jobId) {
        $customerPhone = $this->getCustomerPhone($this->user_id) ?? '+919988722706';
        
        $message = urlencode(
            "🛠️ *Job " . ($this->id ? 'Updated' : 'Created') . "* 🛠️\n" .
            "Job ID: *#$jobId*\n" .
            "Item: *{$this->item_type}*\n" .
            "Issue: *{$this->problem_description}*\n" .
            "Est. Delivery: *{$this->estimated_delivery}*\n" .
            "Status: *{$this->status}*"
        );

        $webhookUrl = "https://hook.us2.make.com/2859um6up676u1vl622hr1i4r7ot2p4g?phone={$customerPhone}&message={$message}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $webhookUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            error_log("Make.com webhook triggered successfully!");
            return true;
        } else {
            error_log("Make.com webhook failed. Response: " . $response);
            return false;
        }
    }
}