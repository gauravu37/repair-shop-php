<?php
/*
if(isset($_GET['phone'])){
	$data = [
		'number' => $_GET['phone'], // include country code
		'message' => $_GET['message'],
	];
}else{
	$data = [
		'number' => '919988722706', // include country code
		'message' => 'Hello from PHP via WhatsApp!',
	];
}

$options = [
    'http' => [
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data),
    ],
];

$context  = stream_context_create($options);
$result = file_get_contents('http://localhost:3000/send-message', false, $context);

echo $result;
*/


function sendMessage($number, $to, $message) {
    $url = 'http://localhost:3000/send-message';
    
    $payload = json_encode([
        'number' => $number,    // e.g., 'number1'
        'to' => $to,            // e.g., '919999999999'
        'message' => $message
    ]);

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($payload)
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}


// ✅ Example usage:

if(isset($_GET['phone'])){
    $response = sendMessage('primeworlcomputer', $_GET['phone'], $_GET['message']);
}else{
    $response = sendMessage('primeworlcomputer', '919988722706', 'Hello from PHP via 919988722706!');
}

//print_r($response['success']);

echo $response['success'];
?>

