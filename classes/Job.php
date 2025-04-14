<?php
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
    public $devices; // Make sure this is declared

    public function __construct($db) {
        $this->conn = $db;
    }

    private function sendWhatsAppNotification($jobId) {
        // Get customer phone from DB (must include country code, e.g., +919876543210)
        $customerPhone = $this->getCustomerPhone($this->user_id);
        if (!$customerPhone) {
            error_log("No phone number found for user ID: {$this->user_id}");
            return false;
        }

        // Timelines.ai API endpoint
        $apiUrl = "https://hook.us2.make.com/2859um6up676u1vl622hr1i4r7ot2p4g?phone=+919988722706&message=hello%20new%20order%20comes";
        
        // Your Timelines.ai API key (store in .env!)
        $apiKey = getenv('82a07157-8821-402f-9c37-344058ffe047');
        
        // WhatsApp Business Number (e.g., "whatsapp:+14123456789")
        $senderId = getenv('whatsapp:+14123456789'); 

        // Message content
        $message = "🛠️ *New Job Created* 🛠️\n" .
                  "Job ID: *#$jobId*\n" .
                  "Item: *{$this->item_type}*\n" .
                  "Issue: *{$this->problem_description}*\n" .
                  "Est. Delivery: *{$this->estimated_delivery}*\n\n" .
                  "We'll notify you with updates!";

        // API request data
        $data = [
            "recipient" => $customerPhone, // e.g., "+919876543210"
            "sender_id" => $senderId,
            "message" => [
                "type" => "text",
                "text" => $message
            ]
        ];

        // Send via cURL
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $apiKey",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            error_log("Timelines.ai WhatsApp message sent!");
            return true;
        } else {
            error_log("Timelines.ai API error: " . $response);
            return false;
        }
    }

    private function getCustomerPhoneNumber($userId) {
        // Implement this method to fetch customer's phone from database
        $query = 'SELECT phone FROM users WHERE id = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['phone'] ?? null;
    }

    public function create() {
        try {
            $this->conn->beginTransaction();
            
            // Insert main job
            $jobQuery = 'INSERT INTO ' . $this->table . ' 
                        (user_id, item_type, problem_description, estimated_delivery, estimated_price) 
                        VALUES (:user_id, :item_type, :problem_description, :estimated_delivery, :estimated_price)';
            $jobStmt = $this->conn->prepare($jobQuery);
            $jobStmt->execute([
                ':user_id' => $this->user_id,
                ':item_type' => $this->item_type,
                ':problem_description' => $this->problem_description,
                ':estimated_delivery' => $this->estimated_delivery,
                ':estimated_price' => $this->estimated_price
            ]);
            
            $jobId = $this->conn->lastInsertId();
            
            // Insert devices if they exist
            if (!empty($this->devices)) {
                // Convert devices to array if it's an object
                $devices = is_object($this->devices) ? json_decode(json_encode($this->devices), true) : $this->devices;
                
                foreach ($devices as $device) {
                    // Access properties correctly whether they come as array or object
                    $deviceType = is_array($device) ? $device['device_type'] : $device->device_type;
                    $problemDesc = is_array($device) ? $device['problem_description'] : $device->problem_description;
                    
                    $deviceQuery = 'INSERT INTO job_devices
                                  (job_id, device_type, problem_description)
                                  VALUES (:job_id, :device_type, :problem_description)';
                    $deviceStmt = $this->conn->prepare($deviceQuery);
                    $deviceStmt->execute([
                        ':job_id' => $jobId,
                        ':device_type' => $deviceType,
                        ':problem_description' => $problemDesc
                    ]);
                }
            }
            // 2. Send WhatsApp notification via Make.com webhook
            $this->sendMakeWebhookNotification($jobId);
            
            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error creating job: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Trigger Make.com webhook for WhatsApp notifications
     */
    private function sendMakeWebhookNotification($jobId) {
        // Get customer phone (from DB or use the one from your example)
        $customerPhone = $this->getCustomerPhone($this->user_id) ?? '+919988722706'; // Fallback to your test number
        
        // Craft the message
        $message = urlencode( // URL-encode to ensure safe HTTP transmission
            "🛠️ *New Job Created* 🛠️\n" .
            "Job ID: *#$jobId*\n" .
            "Item: *{$this->item_type}*\n" .
            "Issue: *{$this->problem_description}*\n" .
            "Est. Delivery: *{$this->estimated_delivery}*"
        );

        // Make.com webhook URL (from your example)
        $webhookUrl = "https://hook.us2.make.com/2859um6up676u1vl622hr1i4r7ot2p4g?phone={$customerPhone}&message={$message}";

        // Send using cURL
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

    public function read() {
        $query = 'SELECT j.*, u.full_name 
                  FROM ' . $this->table . ' j
                  LEFT JOIN users u ON j.user_id = u.id
                  ORDER BY j.job_date DESC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}