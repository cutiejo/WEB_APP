<?php
// Test API Call in Isolation

// Define the phone numbers, message, and API token for the test
$phone_numbers = "phone1,phone2,phone3"; // Replace with actual phone numbers separated by commas
$message = "This is a test message from PHP"; // Test message content
$api_token = "9c5f60c88faf6086782a38bafabd3f9175988cb5"; // Replace with your actual API token

// Define the API URL
$api_url = "https://sms.iprogtech.com/api/v1/sms_messages/send_bulk";

// Construct the URL with query parameters
$url = "$api_url?phone_number=" . urlencode($phone_numbers) . "&api_token=" . urlencode($api_token) . "&message=" . urlencode($message);

// Initialize cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute the request and capture the response
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// Output the results for debugging
echo "HTTP Status Code: " . $http_code . "<br>";
echo "cURL Error (if any): " . $curl_error . "<br>";
echo "API Response: " . $response;
?>
