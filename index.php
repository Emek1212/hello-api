<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_GET['visitor_name'])) {
    readfile('index.html');
    exit;
}

header('Content-Type: application/json');

$visitorName = $_GET['visitor_name'] ?? 'Visitor';
$clientIp = $_SERVER['REMOTE_ADDR'];

function getLocation($ip) {
    if ($ip === '::1' || $ip === '127.0.0.1') {
        $ip = '8.8.8.8';
    }
    $url = "http://ip-api.com/json/$ip";
    $response = file_get_contents($url);
    return json_decode($response, true);
}

function getWeather($city) {
    $apiKey = '54d6dce4828348fd956205012240407';
    $url = "http://api.weatherapi.com/v1/current.json?key=$apiKey&q=$city";
    $response = @file_get_contents($url);
    if ($response === FALSE) {
        return null;
    }
    return json_decode($response, true);
}

$locationData = getLocation($clientIp);
$city = isset($locationData['city']) ? $locationData['city'] : 'New York';
$weatherData = getWeather($city);
$temperature = $weatherData && isset($weatherData['current']['temp_c']) ? $weatherData['current']['temp_c'] : 'unknown';

echo json_encode([
    'client_ip' => $clientIp,
    'location' => $city,
    'greeting' => "Hello, $visitorName!, the temperature is $temperature degrees Celsius in $city"
]);
?>
