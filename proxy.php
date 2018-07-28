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
    header("Cache-Control: public, max-age=2600000");
}

echo($response);

// If we are serving an embed HTML page, we want to add a communicator 
// script so we can send data to the top page.

if (strpos($path, "/h5p/embed/") === 0) {
?>
    <script src="https://files.edx.org/custom-js-example/jschannel.js"></script>
    <script type="text/javascript">

        (function() {

            var state = {};
            
            /**
            Actual event handler for events coming from xAPI (H5P)
            */
            var xAPI = function(event) {
                console.log("Received xAPI event: ", event);

                if (event.result) {
                    // An event that gives us grades
                    state[event.object.id] = event.result;
                    console.log(state);
                }
            };

            window.getGrade = function() {
                return JSON.stringify(state);
            };

            window.getState = getGrade;

            window.setState = function(s) {
                var stateString = arguments.length === 1 ? arguments[0] : arguments[1];
                var state = JSON.parse(stateString);
                
                for (var key in state) {
                    if (state.hasOwnProperty(key)) {
                        document.getElementById("hider").style.display = "";
                        break;
                    }
                }

            }

            /**
            Things we have to do once window is loaded:
            1. Open a channel to edX and send the getGrade, getState, setState functions.
            */
            window.addEventListener("load", function() {
                // Creating the communication channel if the window is embedded
                if (window.parent !== window) {
                    channel = Channel.build({
                        window: window.parent,
                        origin: "*",
                        scope: "JSInput"
                    });

                    channel.bind("getState", getState);
                    channel.bind("setState", setState);
                    channel.bind("getGrade", getGrade);
                }
            });
            
            // This is the communicator script. It is written in JS and is 
            // visible in client side. It lets us communicate with our top window.
            H5P.externalDispatcher.on('xAPI', function (event) {
                xAPI(event.data.statement);
            });
            
            return {
                getState: getState,
                setState: setState,
                getGrade: getGrade
            };
        })();
    </script>


<?php
}
?>