<?php
require_once './middleware/auth.php';

// This will validate the token before proceeding
$user = authenticate();

// Only executed if token is valid
http_response_code(200);
echo json_encode(array(
    "status" => "success",
    "data" => "Protected data",
    "user" => $user
));
?>