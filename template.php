<?php
    /* 
    This is a problem XML generator.
    POST parameters:
        h5p         - the h5p embed id (the number only).
        width       - the width of the activity inside edX. 
                      Default: 100%

        height      - the height of the activity inside edX.
                      Default: 350px

        fail        - the threshold for score 0. Below this score (percentage) the task will be graded 0.
                      Default: 40%

        success     - the threshhold for score 1. Above this score the task will be graded 1.
                      Default: 85%

                      A score between fail and success will result the task being graded 0.5
        
    */

$_POST = $_GET;

if (!isset($_POST["h5p"])) {
    header("HTTP/1.1 400 Bad Request.");
    die("No h5p parameter provided.");
}
$h5p = $_POST["h5p"];

$width = isset($_POST["width"]) ? $_POST["width"] : "100%";
$height = isset($_POST["height"]) ? $_POST["height"] : "350px";
$fail = isset($_POST["fail"]) ? $_POST["fail"] : "40";
$success = isset($_POST["success"]) ? $_POST["success"] : "85";

if (isset($_POST["grade"]) && $_POST["grade"] != "false") {
    $decimal_grade = "True";
}
else {
    $decimal_grade = "False";
}

header("Content-Type: text/plain")
?>
<problem>
    <customresponse cfn="check_function">
        <script type="loncapa/python">
<![CDATA[

import json
import sys
def check_function(e, ans):
    # return {"ok": True, "msg": sys.version}
    response = json.loads(ans)
    answer = json.loads(response["answer"])
    max_score, achieved = 0, 0
    for el, res in answer.iteritems():
        if "score" in res:
            max_score += res["score"]["max"]
            achieved += res["score"]["raw"]

    grade = 0 if max_score == 0 else (float(achieved) * 100 / float(max_score))
    if grade <= <?php echo $fail; ?>:
        ok = False
    elif grade < <?php echo $success; ?>:
        ok = "Partial"
    else:
        ok = True

    if <?php echo $decimal_grade;?>:
        return {"ok": ok, "msg": "Score: " + str(round(grade)) + "%", "grade_decimal": round(grade) / 100.0}
    else:
        return {"ok": ok, "msg": "Score: " + str(round(grade)) + "%"}

]]>
        </script>

        <jsinput
            gradefn="window.getGrade"
            get_statefn="window.getState"
            set_statefn="window.setState"
            initial_state="false"
            width="<?php echo $width; ?>"
            height="<?php echo $height; ?>"
            html_file="http://localhost:8080/activity?a=<?php echo $h5p;?>"
            sop="false"
            />
    </customresponse>
</problem>