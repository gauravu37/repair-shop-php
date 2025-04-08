<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/Database.php';
require_once 'classes/Auth.php';

$database = new Database();
$db = $database->connect();

$auth = new Auth($db);

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->email) && !empty($data->password)) {
    $result = $auth->login($data->email, $data->password);
    if($result['status'] === "success") {
        http_response_code(200);
        echo json_encode($result);
    } else {
        http_response_code(401);
        echo json_encode($result);
    }
} else {
    http_response_code(400);
    echo json_encode(array("status" => "error", "message" => "Email and password are required"));
}
?>