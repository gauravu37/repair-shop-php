<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}


require_once '../config/Database.php';
require_once '../classes/Job.php';
require_once 'classes/Auth.php';

$database = new Database();
$db = $database->connect();
$job = new Job($db);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        if (isset($_GET['payment'])) {
            $data = json_decode(file_get_contents("php://input"));
            
            if ($job->updatePayment(
                $data->status,
                $data->method ?? null,
                $data->upi_link ?? null
            )) {
                http_response_code(200);
                echo json_encode(["message" => "Payment updated"]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Payment update failed"]);
            }
        }


        $data = json_decode(file_get_contents("php://input"));
        
        $job->user_id = $data->user_id;
        $job->item_type = $data->item_type;
        $job->problem_description = $data->problem_description;
        $job->estimated_delivery = $data->estimated_delivery;
        $job->estimated_price = $data->estimated_price;
        $job->devices = $data->devices;
        
        if($job->create()) {
            http_response_code(201);
            echo json_encode(array("message" => "Job created successfully."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to create job."));
        }
        break;
        
    case 'GET':
        //To download invoices
        if (isset($_GET['invoice'])) {
            // Generate invoice
            $job->id = $_GET['id'];
            if ($job->generateInvoice()) {
                http_response_code(200);
                echo json_encode(array(
                    "message" => "Invoice generated and sent",
                    "invoice_path" => $job->getInvoicePath()
                ));
            } else {
                http_response_code(500);
                echo json_encode(array("message" => "Failed to generate invoice"));
            }
        }
        elseif (isset($_GET['download_invoice'])) {
            // Download invoice
            $job->id = $_GET['id'];
            $job->downloadInvoice($job->id);
        }

        if (isset($_GET['totalpayment'])) {
            $type = $_GET['totalpayment'];
            $total = $job->totalPayment($type);
            echo $total[0]['total_paid_amount'];
            break;
        }

        // Check if an ID was provided in the URL
        if(isset($_GET['id'])) {
            // Get single job
            $job->id = $_GET['id'];
            
            if($job->readSingle()) {
                http_response_code(200);
                echo json_encode(array(
                    "id" => $job->id,
                    "user_id" => $job->user_id,
                    "item_type" => $job->item_type,
                    "problem_description" => $job->problem_description,
                    "estimated_delivery" => $job->estimated_delivery,
                    "estimated_price" => $job->estimated_price,
                    "status" => $job->status,
                    "devices" => $job->devices, // Make sure your Job class loads devices
                    "payment_status" => $job->payment_status,
                    "payment_method" => $job->payment_method,
                    "upi_link" => $job->upi_link
                ));
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Job not found."));
            }
        } else {
            // Get all jobs (existing code)
            $result = $job->read();
            $num = $result->rowCount();
            
            if($num > 0) {
                $jobs_arr = array();
                $jobs_arr["data"] = array();
                
                while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    
                    $job_item = array(
                        "id" => $id,
                        "user_id" => $user_id,
                        "full_name" => $full_name,
                        "job_date" => $job_date,
                        "item_type" => $item_type,
                        "problem_description" => $problem_description,
                        "estimated_delivery" => $estimated_delivery,
                        "estimated_price" => $estimated_price,
                        "status" => $status,
                        "payment_status" => $payment_status
                    );
                    
                    array_push($jobs_arr["data"], $job_item);
                }
                
                http_response_code(200);
                echo json_encode($jobs_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "No jobs found."));
            }
        }
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(["message" => "ID is required"]);
            break;
        }

        $job->id = $id;
        $job->item_type = $data->item_type;
        $job->problem_description = $data->problem_description;
        $job->estimated_delivery = $data->estimated_delivery;
        $job->estimated_price = $data->estimated_price;
        $job->status = $data->status ?? 'pending'; // Default status if not provided
        $job->devices = $data->devices ?? []; // Handle devices if provided
        $job->payment_status = $data->payment_status ?? null;
        $job->payment_method = $data->payment_method ?? null;
        $job->upi_link = $data->upi_link ?? null;
        
        if($job->update()) {
            http_response_code(200);
            echo json_encode(array("message" => "Job updated."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to update job."));
        }
        break;
        
    case 'DELETE':
        // Optional: Add delete functionality if needed
        $data = json_decode(file_get_contents("php://input"));
        $job->id = $data->id;
        
        if($job->delete()) {
            http_response_code(200);
            echo json_encode(array("message" => "Job deleted."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to delete job."));
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed."));
        break;
}
?>