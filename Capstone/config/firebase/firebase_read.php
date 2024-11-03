<?php
function readFromFirebase($path) {
    $url = "https://svmrfid-default-rtdb.firebaseio.com/$path.json";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

// Example usage
$response = readFromFirebase("messages");
print_r($response);
