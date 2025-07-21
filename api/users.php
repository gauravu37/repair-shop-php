<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../classes/User.php';

$database = new Database();
$db = $database->connect();

$user = new User($db);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Read users
        $stmt = $user->read();
        $num = $stmt->rowCount();

        $Totalread = $user->Totalread();
        if(isset($_GET['totalusers']) && $_GET['totalusers'] == '1'){
            echo $Totalread;
            exit;
        }

        if($num > 0) {
            $users_arr = array();
            $users_arr["records"] = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $user_item = array(
                    "id" => $id,
                    "username" => $username,
                    "full_name" => $full_name,
                    "email" => $email,
                    "phone" => $phone,
                    "whatsapp" => $whatsapp,
                    "address" => $address,
                    "user_type" => $user_type,
                    "created_at" => $created_at
                );
                array_push($users_arr["records"], $user_item);
            }
            http_response_code(200);
            echo json_encode($users_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "No users found."));
        }
        break;
    case 'POST':
        // Create user
        $data = json_decode(file_get_contents("php://input"));

        $user->full_name = $data->full_name;
        $user->email = $data->email;
        $user->phone = $data->phone;
        $user->whatsapp = $data->phone;
        $user->address = $data->address;
        $user->user_type = 'customer';
        $user->password = $data->full_name.'@12390';

        if($user->create()) {
            http_response_code(201);
            echo json_encode(array("message" => "User was created."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to create user."));
        }
        break;
    // Add cases for PUT and DELETE
}
?>