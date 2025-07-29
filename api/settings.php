<?php

// settings.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../classes/settings.php';

$database = new Database();
$db = $database->connect();

$setting = new Setting($db);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $stmt = $setting->read();
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        
        http_response_code(200);
        echo json_encode($settings);
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        
        $setting->business_name = $data->businessName;
        $setting->contact_number = $data->contactNumber;
        $setting->logo_path = $data->logoPath;
        $setting->qr_code_path = $data->qrCodePath;
        
        if($setting->update()) {
            http_response_code(200);
            echo json_encode(array("message" => "Settings were updated."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to update settings."));
        }
        break;
        
    case 'POST':
        if (isset($_FILES['file'])) {
            $target_dir = "uploads/";
            $fileType = strtolower(pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION));
            $newFileName = uniqid() . '.' . $fileType;
            $target_file = $target_dir . $newFileName;
            
            // Check if image file is a actual image
            $check = getimagesize($_FILES["file"]["tmp_name"]);
            if($check === false) {
                http_response_code(400);
                echo json_encode(array("message" => "File is not an image."));
                exit;
            }
            
            // Check file size (5MB max)
            if ($_FILES["file"]["size"] > 5000000) {
                http_response_code(400);
                echo json_encode(array("message" => "File is too large."));
                exit;
            }
            
            // Allow certain file formats
            if($fileType != "jpg" && $fileType != "png" && $fileType != "jpeg" && $fileType != "gif" ) {
                http_response_code(400);
                echo json_encode(array("message" => "Only JPG, JPEG, PNG & GIF files are allowed."));
                exit;
            }
            
            if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
                http_response_code(201);
                echo json_encode(array(
                    "message" => "File uploaded successfully.",
                    "path" => $target_file
                ));
            } else {
                http_response_code(500);
                echo json_encode(array("message" => "Error uploading file."));
            }
        }
        break;
}

?>