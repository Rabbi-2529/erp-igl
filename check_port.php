<?php
$url = "http://sms.iglweb.com/api/v1/info"; // Example endpoint, replace with actual

// Initialize cURL session
$ch = curl_init($url);

// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute the request
$response = curl_exec($ch);

// Get HTTP status code
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Close cURL session
curl_close($ch);

// Print the result
if ($httpCode == 200) {
    echo "API Info Success:\n" . $response;
} else {
    echo "Error: HTTP Status Code $httpCode\n";
    echo "Response:\n" . $response;
}
?>