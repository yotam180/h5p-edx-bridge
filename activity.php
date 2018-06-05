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

    <script type="text/javascript">
        window.addEventListener("message", function(e) {
            if (e.data["type"] && e.data.type == "xAPI") {
                xAPI(e.data.event);
            }
        }, false);

        window.xAPI = function(event) {
            console.log("Received xAPI event: ", event);
        };
    </script>
</head>
<body
    style="margin: 0;">

    <!-- H5P activity iframe -->
    <iframe width="100%" height="100%" frameBorder="0" src="/h5p/embed/<?php echo($_GET["a"]); ?>" />
</body>
</html>