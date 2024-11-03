<?php
function writeToFirebase($path, $data) {
    $url = "https://svmrfid-default-rtdb.firebaseio.com/$path.json";
    
    $jsonData = json_encode($data);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

// Example usage
$data = [
    "name" => "Jane Doe",
    "message" => "Hello from Firebase!"
];
$response = writeToFirebase("messages", $data);
echo "Response from Firebase: " . $response;
