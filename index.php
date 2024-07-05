<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_GET['visitor_name'])) {
    // Serve the HTML file if no query parameter is provided
    readfile('index.html');
    exit;
}

header('Content-Type: application/json');

$visitorName = $_GET['visitor_name'] ?? 'Visitor';
$clientIp = $_SERVER['REMOTE_ADDR'];

// Function to get location based on IP
function getLocation($ip) {
    // For localhost testing, we'll use a default IP that resolves to a known location
    if ($ip === '::1' || $ip === '127.0.0.1') {
        $ip = '8.8.8.8'; // Google's public DNS server IP
    }

    $url = "http://ip-api.com/json/$ip";
    $response = @file_get_contents($url);

    // Check if the response is valid
    if ($response === FALSE) {
        error_log("Failed to fetch location data for IP: $ip");
        return null;
    }

    return json_decode($response, true);
}


function getWeather($city) {
    $apiKey = '54d6dce4828348fd956205012240407'; 
    $url = "http://api.weatherapi.com/v1/current.json?key=$apiKey&q=" . urlencode($city);
    
    $response = @file_get_contents($url);

    if ($response === FALSE) {
        error_log("Failed to fetch weather data from WeatherAPI for city: $city. URL: $url");
        return null;
    }

 
    $weatherData = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Failed to decode JSON response: " . json_last_error_msg());
        return null;
    }

    return $weatherData;
}

// Get location data
$locationData = getLocation($clientIp);

// Check if the location data has a city
if (isset($locationData['city'])) {
    $city = $locationData['city'];
} else {
    // Default to New York if city is not available
    $city = 'New York';
}

// Get weather data
$weatherData = getWeather($city);

// Check if the weather data is available
if (isset($weatherData['current']['temp_c'])) {
    $temperature = $weatherData['current']['temp_c'];
} else {
    // Default temperature if weather data is not available
    $temperature = 'unknown';
}

// Respond with the required data
echo json_encode([
    'client_ip' => $clientIp,
    'location' => $city,
    'greeting' => "Hello, $visitorName!, the temperature is $temperature degrees Celsius in $city"
]);
?>
