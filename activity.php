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
</head>
<body>
    This is where the activity comes.
</body>
</html>