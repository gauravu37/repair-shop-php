<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config/Database.php';
require_once '../classes/RepairCase.php'; // Updated filename
require_once 'classes/Auth.php';

$database = new Database();
$db = $database->connect();
$repairCase = new RepairCase($db); // Updated variable name

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        $repairCase->user_id = $data->user_id;
        $repairCase->item_type = $data->item_type;
        $repairCase->problem_description = $data->problem_description;
        $repairCase->estimated_delivery = $data->estimated_delivery;
        $repairCase->estimated_price = $data->estimated_price;
        
        if($repairCase->create()) {
            http_response_code(201);
            echo json_encode(array("message" => "Case created successfully."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to create case."));
        }
        break;
        
    // ... rest of your switch cases
}
?>