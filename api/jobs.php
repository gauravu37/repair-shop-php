<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config/Database.php';
require_once '../classes/Job.php';
require_once 'classes/Auth.php';

$database = new Database();
$db = $database->connect();
$job = new Job($db);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
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
                    "status" => $status
                );
                
                array_push($jobs_arr["data"], $job_item);
            }
            
            http_response_code(200);
            echo json_encode($jobs_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "No jobs found."));
        }
        break;
}
?>