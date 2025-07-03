<?php
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


class Job {
    private $conn;
    private $table = 'jobs';

    public $id;
    public $user_id;
    public $job_date;
    public $item_type;
    public $problem_description;
    public $serial_number;
    public $needs_replacement;
    public $replacement_serial_number;
    public $estimated_delivery;
    public $estimated_price;
    public $status;
    public $devices;
    public $payment_status;
    public $payment_method;
    public $upi_link;
    public $toalPaidPayment;
    public $totalJobCount;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        try {
            $this->conn->beginTransaction();
            
            // Insert main job
            $jobQuery = 'INSERT INTO ' . $this->table . ' 
                        (user_id, item_type, problem_description, estimated_delivery, serial_number,needs_replacement,replacement_serial_number, estimated_price, status) 
                        VALUES (:user_id, :item_type, :problem_description, :estimated_delivery, :serial_number,:needs_replacement,:replacement_serial_number, :estimated_price, :status)';
            
            try {
                $jobStmt = $this->conn->prepare($jobQuery);
                $jobStmt->execute([
                    ':user_id' => $this->user_id,
                    ':item_type' => $this->item_type,
                    ':problem_description' => $this->problem_description,
                    ':serial_number' => $this->serial_number,
                    ':needs_replacement' => $this->needs_replacement,
                    ':replacement_serial_number' => $this->replacement_serial_number,
                    ':estimated_delivery' => $this->estimated_delivery,
                    ':estimated_price' => $this->estimated_price,
                    ':status' => $this->status ?? 'pending'
                ]);
            } catch (PDOException $e) {
                echo 'Database Error: ' . $e->getMessage();
            }
            
            
            /*            $jobStmt = $this->conn->prepare($jobQuery);
            $jobStmt->execute([
                ':user_id' => $this->user_id,
                ':item_type' => $this->item_type,
                ':problem_description' => $this->problem_description,
                ':serial_number' => $this->serial_number,
                ':needs_replacement' => $this->needs_replacement,
                ':replacement_serial_number' => $this->replacement_serial_number,
                ':estimated_delivery' => $this->estimated_delivery,
                ':estimated_price' => $this->estimated_price,
                ':status' => $this->status ?? 'pending'
            ]);*/
           
            $jobId = $this->conn->lastInsertId();
            
            // Insert devices
            if (!empty($this->devices)) {
                $devices = is_object($this->devices) ? json_decode(json_encode($this->devices), true) : $this->devices;
                
                foreach ($devices as $device) {
                    $deviceType = is_array($device) ? $device['device_type'] : $device->device_type;
                    $problemDesc = is_array($device) ? $device['problem_description'] : $device->problem_description;
                    $estimatedPrice = is_array($device) ? $device['estimated_price'] : $device->estimated_price;
                    $serial_number = is_array($device) ? $device['serial_number'] : $device->serial_number;
                    $needs_replacement = is_array($device) ? $device['needs_replacement'] : $device->needs_replacement;
                    $replacement_serial_number = is_array($device) ? $device['replacement_serial_number'] : $device->replacement_serial_number;

                    $deviceQuery = 'INSERT INTO job_devices
                                  (job_id, device_type, problem_description, estimated_price, serial_number,needs_replacement,replacement_serial_number)
                                  VALUES (:job_id, :device_type, :problem_description, :estimated_price, :serial_number,:needs_replacement,:replacement_serial_number)';
                    $deviceStmt = $this->conn->prepare($deviceQuery);
                    $deviceStmt->execute([
                        ':job_id' => $jobId,
                        ':device_type' => $deviceType,
                        ':problem_description' => $problemDesc,
                        ':estimated_price' => $estimatedPrice,
                        ':serial_number' => $serial_number,
                        ':needs_replacement' => $needs_replacement,
                        ':replacement_serial_number' => $replacement_serial_number
                    ]);
                }
            }
            
            // Send WhatsApp notification
            $this->sendMakeWebhookNotification($jobId,'Created');
            
            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error creating job: " . $e->getMessage());
            return false;
        }
    }

    public function read() {
        $query = 'SELECT j.*, u.full_name, p.status as payment_status, p.payment_method, p.upi_link 
                 FROM ' . $this->table . ' j
                 LEFT JOIN users u ON j.user_id = u.id
                 LEFT JOIN payments p ON j.id = p.job_id
                  ORDER BY j.job_date DESC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /*public function readSingle() {
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
    }*/


    public function readSingle() {
        $query = 'SELECT j.*, u.full_name, p.status as payment_status, p.payment_method, p.upi_link 
                 FROM ' . $this->table . ' j
                 LEFT JOIN users u ON j.user_id = u.id
                 LEFT JOIN payments p ON j.id = p.job_id
                 WHERE j.id = ? LIMIT 1';
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->user_id = $row['user_id'];
            $this->item_type = $row['item_type'];
            $this->problem_description = $row['problem_description'];
            $this->serial_number = $row['serial_number'];
            $this->replacement_serial_number = $row['replacement_serial_number'];
            $this->estimated_delivery = $row['estimated_delivery'];
            $this->estimated_price = $row['estimated_price'];
            $this->status = $row['status'];
            $this->job_date = $row['job_date'];
            
            // Payment information
            $this->payment_status = $row['payment_status'] ?? 'pending';
            $this->payment_method = $row['payment_method'] ?? null;
            $this->upi_link = $row['upi_link'] ?? null;
            
            // Load devices
            $this->loadDevices();
            return true;
        }
        
        return false;
    }

    public function getPaymentInfo() {
        $query = 'SELECT status as payment_status, payment_method, upi_link 
                  FROM payments 
                  WHERE job_id = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ?: [
            'payment_status' => 'pending',
            'payment_method' => null,
            'upi_link' => null
        ];
    }

    private function loadDevices() {
        $query = 'SELECT * FROM job_devices WHERE job_id = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $this->devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function totalPayment($type = "paid") {
        $query = "SELECT SUM(amount) AS total_paid_amount FROM payments WHERE status = '$type'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //if($type == 'paid'){
            return $this->toalPaidPayment = $result;
        //}
    }

    public function totalJobsCount($status="all", $month="") {
        if($status == 'all'){
            if($month != ''){
                $query = "SELECT COUNT(id) AS total_jobs FROM jobs WHERE MONTH(job_date) = MONTH(CURRENT_DATE()) AND YEAR(job_date) = YEAR(CURRENT_DATE())";
            }else{
                $query = "SELECT COUNT(id) AS total_jobs FROM jobs";
            }
        }else{
            if($month != ''){
                $query = "SELECT COUNT(id) AS total_jobs FROM jobs WHERE MONTH(job_date) = MONTH(CURRENT_DATE()) AND YEAR(job_date) = YEAR(CURRENT_DATE()) AND status = '$status'";
            }else{
                $query = "SELECT COUNT(id) AS total_jobs FROM jobs WHERE status = '$status'";
            }
        }
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->totalJobCount = $result;
    }

    /*// Main update method
    public function update() {
        try {
            $this->conn->beginTransaction();
            
            // Get current status before updating
            $oldStatus = $this->getCurrentStatus();
            
            // Update job details
            $query = 'UPDATE ' . $this->table . ' 
                     SET item_type = :item_type,
                         problem_description = :problem_description,
                         estimated_delivery = :estimated_delivery,
                         estimated_price = :estimated_price,
                         status = :status
                     WHERE id = :id';
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':item_type' => $this->item_type,
                ':problem_description' => $this->problem_description,
                ':estimated_delivery' => $this->estimated_delivery,
                ':estimated_price' => $this->estimated_price,
                ':status' => $this->status,
                ':id' => $this->id
            ]);
            
            // Update devices
            $this->updateDevices();
            
            // Generate invoice if status changed to completed
            if ($oldStatus !== 'completed' && $this->status === 'completed') {
                $this->generateInvoice();
            }
            
            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error updating job: " . $e->getMessage());
            return false;
        }
    }*/

    // Main update method
    public function update() {
        try {
            $this->conn->beginTransaction();
            
            // Get current status before updating
            $oldStatus = $this->getCurrentStatus();
            
            // Update job details
            $query = 'UPDATE ' . $this->table . ' 
                     SET item_type = :item_type,
                         problem_description = :problem_description,
                         estimated_delivery = :estimated_delivery,
                         estimated_price = :estimated_price,
                         serial_number = :serial_number,
                         needs_replacement = :needs_replacement,
                         replacement_serial_number = :replacement_serial_number,
                         status = :status
                     WHERE id = :id';
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':item_type' => $this->item_type,
                ':problem_description' => $this->problem_description,
                ':estimated_delivery' => $this->estimated_delivery,
                ':serial_number' => $this->serial_number,
                ':needs_replacement' => $this->needs_replacement,
                ':replacement_serial_number' => $this->replacement_serial_number,
                ':estimated_price' => $this->estimated_price,
                ':status' => $this->status,
                ':id' => $this->id
            ]);
            
            // Update devices
            $this->updateDevices();

            // Update payment details
            $this->updatePaymentDetails();
            
            // Generate invoice if status changed to completed
            if ($oldStatus !== 'completed' && $this->status === 'completed') {
                $this->generateInvoice();
                $this->sendMakeWebhookNotification($this->id,'Updated');
            }
            
            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error updating job: " . $e->getMessage());
            return false;
        }
    }

    private function updatePaymentDetails() {
        if ($this->payment_status !== null) {
            // Check if payment record exists
            $stmt = $this->conn->prepare("SELECT id FROM payments WHERE job_id = ?");
            $stmt->execute([$this->id]);
            
            if ($stmt->rowCount() === 0) {
                $this->createPaymentRecord($this->calculateTotalPrice());
            }
    
            // Update payment information
            $query = 'UPDATE payments SET
                      status = :status,
                      payment_method = :method,
                      upi_link = :upi
                      WHERE job_id = :job_id';
                      
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':status' => $this->payment_status,
                ':method' => $this->payment_method,
                ':upi' => $this->upi_link,
                ':job_id' => $this->id
            ]);
        }
    }

    private function getCustomerPhone($userId) {
        $query = 'SELECT phone FROM users WHERE id = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['phone'] ?? null;
    }

    private function sendMakeWebhookNotification($jobId, $type = "") {
        $customerPhone = $this->getCustomerPhone($this->user_id) ?? '+919988722706';
        $customerPhone = $this->formatIndianMobile($customerPhone);
        //echo $customerPhone; exit;
        //$customerPhone = '919988722706';

        if($type == 'Created'){
            $message = urlencode(
                "🛠️ *Job " . ($this->id ? 'Updated' : 'Created') . "* 🛠️\n" .
                "Job ID: *#$jobId*\n" .
                "Item: *{$this->item_type}*\n" .
                "Issue: *{$this->problem_description}*\n" .
                "Est. Delivery: *{$this->estimated_delivery}*\n" .
                "Status: *{$this->status}*"
            );
        }else if($type == 'Updated'){
            $message = urlencode(
                "🛠️ *Job Updated * 🛠️\n" .
                "Job ID: *#$jobId*\n" .
                "Item: *{$this->item_type}*\n" .
                "Issue: *{$this->problem_description}*\n" .
                "Est. Delivery: *{$this->estimated_delivery}*\n" .
                "Status: *{$this->status}*"
            );
        }

       // https://hook.us2.make.com/2859um6up676u1vl622hr1i4r7ot2p4g?phone=+919988722706&message=hello%20new%20order%20comes

        //$webhookUrl = "https://hook.us2.make.com/2859um6up676u1vl622hr1i4r7ot2p4g?phone={$customerPhone}&message={$message}";

        $webhookUrl = "http://localhost/computer_repair_php/repair-shop-php/api/whatsapp.php?phone={$customerPhone}&message={$message}";

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
    

    public function formatIndianMobile($mobile) {
        // Remove any non-numeric characters
        $mobile = preg_replace('/\D/', '', $mobile);

        // If the number already starts with 91, return as is
        if (strpos($mobile, '91') === 0) {
            return $mobile;
        }

        // Add 91 in front
        return '91' . $mobile;
    }
    

    private function getCurrentStatus() {
        $query = 'SELECT status FROM ' . $this->table . ' WHERE id = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['status'] ?? null;
    }

    private function updateDevices() {
        // Delete existing devices
        $deleteQuery = 'DELETE FROM job_devices WHERE job_id = ?';
        $deleteStmt = $this->conn->prepare($deleteQuery);
        $deleteStmt->execute([$this->id]);
        
        // Insert new devices
        if (!empty($this->devices)) {
            foreach ($this->devices as $device) {
                $device = (array)$device;
                $deviceQuery = 'INSERT INTO job_devices
                              (job_id, device_type, problem_description, estimated_price, serial_number, needs_replacement, replacement_serial_number)
                              VALUES (:job_id, :device_type, :problem_description, :estimated_price, :serial_number, :needs_replacement, :replacement_serial_number)';
                $deviceStmt = $this->conn->prepare($deviceQuery);
                $deviceStmt->execute([
                    ':job_id' => $this->id,
                    ':device_type' => $device['device_type'],
                    ':problem_description' => $device['problem_description'],
                    ':serial_number' => $device['serial_number'],
                    ':needs_replacement' => $device['needs_replacement'],
                    ':replacement_serial_number' => $device['replacement_serial_number'],
                    ':estimated_price' => $device['estimated_price']
                ]);
            }
        }
    }

    public function getInvoicePath(){
        $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad($this->id, 5, '0', STR_PAD_LEFT);
        $filename = "/invoices/$invoiceNumber.pdf";
        return __DIR__ .$filename;
    }

    public function generateInvoice() {
        try {
            // Generate invoice number
            $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad($this->id, 5, '0', STR_PAD_LEFT);
            
            // Calculate total amount
            $totalAmount = $this->calculateTotalPrice();
            
            // Generate PDF
            $pdfPath = $this->generatePdf($invoiceNumber, $totalAmount);
            
            // Save invoice record
            $query = 'INSERT INTO invoices 
                     (job_id, invoice_number, total_amount, pdf_path)
                     VALUES (?, ?, ?, ?)';
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$this->id, $invoiceNumber, $totalAmount, $pdfPath]);
            
            // Create payment record if not exists
            $this->createPaymentRecord($totalAmount);
            
            // Send email
            $this->sendInvoiceEmail();
            
            return true;
        } catch (Exception $e) {
            error_log("Invoice generation failed: " . $e->getMessage());
            return false;
        }
    }

    public function createPaymentRecord($amount) {
        try {
            // Check if payment record already exists
            $query = 'SELECT id FROM payments WHERE job_id = ?';
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$this->id]);
            
            if ($stmt->rowCount() === 0) {
                $insertQuery = 'INSERT INTO payments 
                               (job_id, amount, status) 
                               VALUES (?, ?, ?)';
                $insertStmt = $this->conn->prepare($insertQuery);
                $insertStmt->execute([
                    $this->id,
                    $amount,
                    'pending' // Default status
                ]);
                return true;
            }
            return true; // Record already exists
        } catch (PDOException $e) {
            error_log("Error creating payment record: " . $e->getMessage());
            return false;
        }
    }

    
    public function calculateTotalPrice() {
        try {
            // If we already have devices loaded in the object
            /*if (!empty($this->devices)) {
                return array_reduce($this->devices, function($sum, $device) {
                    return $sum + (float)($device['estimated_price'] ?? 0);
                }, 0);
            }*/
            
            // Otherwise query the database
            $query = 'SELECT SUM(estimated_price) as total FROM job_devices WHERE job_id = ?';
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$this->id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (float)($result['total'] ?? 0);
        } catch (PDOException $e) {
            error_log("Error calculating total price: " . $e->getMessage());
            return 0;
        }
    }

    /*
    public function calculateTotalPrice() {
        return (float)(10000);
    }*/
    
    private function generatePdf($invoiceNumber, $totalAmount) {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // PDF setup and content
        $pdf->SetTitle("Invoice $invoiceNumber");
        $pdf->AddPage();
        
        $html = "<h1>Invoice #$invoiceNumber</h1>
                <h3>Job ID: {$this->id}</h3>
                <p>Date: " . date('Y-m-d') . "</p>
                <table border='1'>
                    <tr>
                        <th>Device</th>
                        <th>Problem</th>
                        <th>Serial number</th>
                        <th>Price</th>
                    </tr>";
        $html .= "<tr>
                    <td>hii</td>
                    <td>pop</td>
                    <td>lol</td>
                </tr>";            
        //print_r($this->devices);
        foreach ($this->devices as $device) {
            //echo $device->device_type;
            $html .= "<tr>
                        <td>{$device->device_type}</td>
                        <td>{$device->problem_description}</td>
                        <td>{$device->serial_number}</td>
                        <td>{$device->estimated_price}</td>
                    </tr>";
        }
        
        $html .= "<tr>
                    <td colspan='2'>Total</td>
                    <td>$totalAmount</td>
                </tr>
                </table>";
        
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Save to file
        $filename = "invoices/$invoiceNumber.pdf";
        $pdf->Output(__DIR__ . "/$filename", 'F');
        
        return $filename;
    }

    private function sendInvoiceEmail() {
        $mail = new PHPMailer(true);
        
        try {
            //$customerEmail = $this->getCustomerEmail();
            //$paymentStatus = $this->getPaymentStatus();

            $customerEmail = 'er.gauravmittal1989@gmail.com';
            $paymentStatus = 'paid';
            
            // Email configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'prontodevtesting@gmail.com';
            $mail->Password = 'Gaurav#_&678Pronto'; // Use App Password here, NOT your Gmail password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // or 'tls'
            $mail->Port = 587;

            $mail->setFrom('prontodevtesting@gmail.com', 'Repair Shop');
            $mail->addAddress($customerEmail);
            $mail->Subject = "Your Invoice #{$this->id}";

            // Add PDF attachment
            $mail->addAttachment(__DIR__ . "/../invoices/INV-*.pdf");
            
            // Email body
            $body = "<h2>Invoice #{$this->id}</h2>";
            if ($paymentStatus === 'pending') {
                $upiLink = $this->getUpiLink();
                $body .= "<p>Payment due: <a href='$upiLink'>Pay Now</a></p>";
            } else {
                $body .= "<p>Payment received. Thank you!</p>";
            }
            
            $mail->isHTML(true);
            $mail->Body = $body;
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email error: " . $e->getMessage());
            return false;
        }
    }

    private function getCustomerEmail() {
        try {
            $query = 'SELECT email FROM users WHERE id = ?';
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$this->user_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['email'] ?? null;
        } catch (PDOException $e) {
            error_log("Error fetching customer email: " . $e->getMessage());
            return null;
        }
    }
    
    private function getPaymentStatus() {
        try {
            $query = 'SELECT status FROM payments WHERE job_id = ?';
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$this->id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['status'] ?? 'pending';
        } catch (PDOException $e) {
            error_log("Error fetching payment status: " . $e->getMessage());
            return 'pending';
        }
    }
    
}