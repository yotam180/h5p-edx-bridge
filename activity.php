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

            var getGrade = function() {
                return JSON.stringify(state);
            };

            var getState = getGrade;

            var setState = function(s) {
                console.log("SetState ", s);
            }

            /**
            Things we have to do once window is loaded:
            1. Open a channel to edX and send the getGrade, getState, setState functions.
            */
            window.addEventListener("load", function() {
                
            });
        })();
    </script>
</head>
<body
    style="margin: 0;">

    <!-- H5P activity iframe -->
    <iframe width="100%" height="100%" frameBorder="0" src="/h5p/embed/<?php echo($_GET["a"]); ?>" />
</body>
</html>