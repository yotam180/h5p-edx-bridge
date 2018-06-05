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

foreach ($http_response_header as $header) {
    header($header);
    header("Access-Control-Allow-Origin: *");
}

echo($response);

// If we are serving an embed HTML page, we want to add a communicator 
// script so we can send data to the top page.

if (strpos($path, "/h5p/embed/") === 0) {
?>
<script type="text/javascript">
    // This is the communicator script. It is written in JS and is 
    // visible in client side. It lets us communicate with our top window.
    // Console loggings are for debug purposes. Can be removed.
    H5P.externalDispatcher.on('xAPI', function (event) {
        top.postMessage({"type": "xAPI", event: event.data.statement}, "*");
        console.log(event.data.statement);
    });
</script>
<?php
}
?>