<!--

This is the HTML page hosting the activity.
Inside this page there are:
1. The iFrame of the H5P activity
2. Javascript for communicating with the edX platform

-->

<?php
if (!isset($_GET["a"])) {
    die("Activity unlisted.");
}
?>

<html>
<head>
    <title>H5P activity host</title>

    <script src="https://files.edx.org/custom-js-example/jschannel.js"></script>

    <script type="text/javascript">
        (function() {
            
            /**
            Listening to messages from the iframe
            */
            window.addEventListener("message", function(e) {
                if (e.data["type"] && e.data.type == "xAPI") {
                    xAPI(e.data.event);
                }
            }, false);

            var state = {};
            
            /**
            Actual event handler for events coming from iframe xAPI (H5P)
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
                console.log(state);
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

            return {
                getState: getState,
                setState: setState,
                getGrade: getGrade
            };
        })();
    </script>
</head>
<body
    style="margin: 0;">

    <!-- H5P activity iframe -->
    <iframe width="100%" height="100%" frameBorder="0" src="/h5p/embed/<?php echo($_GET["a"]); ?>"></iframe>

    <!-- For hiding the activity -->
    <div id="hider" style="position: fixed; top: 0; bottom: 0; left: 0; right: 0; background-color: rgba(100, 100, 100, 0.3); text-align: center; font-size: 30px; color: black; font-family: Arial;">
        <div>
            You have already completed this activity. Click to redo it.
        </div>
    </div>
</body>
</html>