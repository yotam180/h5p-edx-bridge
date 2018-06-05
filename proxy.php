<?php
/**
 * 
 * This is a proxy script. It hides http://h5p.org behind it
 * and adds accessibility headers so we can actually interact
 * with our H5P content.
 * 
 */

// Taking the path we want to redirect to
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Building the URL
$url = "http://h5p.org" . $path;

// Sending our HTTP request
$response = file_get_contents($url, false);

foreach ($http_response_header as $key => $val) {
    header($key . ": " . $val);
    header("Access-Control-Allow-Origin: *");
}

echo($response);

?>