<?php
require_once '../config/Database.php';
require_once '../classes/Auth.php';

function authenticate() {
    $headers = getallheaders();
    $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
    
    if(!$token) {
        http_response_code(401);
        echo json_encode(array("status" => "error", "message" => "Authorization token required"));
        exit();
    }

    $database = new Database();
    $db = $database->connect();
    
    $auth = new Auth($db);
    $user = $auth->validateToken($token);
    
    if(!$user) {
        http_response_code(401);
        echo json_encode(array("status" => "error", "message" => "Invalid or expired token"));
        exit();
    }
    
    return $user;
}
?>