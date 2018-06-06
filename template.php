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

    grade = 0 if max_score == 0 else (float(achieved) / float(max_score))
    if grade < 0.4:
        return {"ok": False, "msg": "Score: " + str(round(grade * 100)) + "%"}
    elif grade < 0.85:
        return {"ok": "Partial", "msg": "Score: " + str(int(round(grade * 100))) + "%"}
    else:
        return {"ok": True, "msg": "Score: " + str(round(grade * 100)) + "%"}

]]>
        </script>

        <jsinput
            gradefn="window.getGrade"
            get_statefn="window.getState"
            set_statefn="window.setState"
            initial_state="false"
            width="100%"
            height="325"
            html_file="http://localhost:8080/activity?a=251488"
            title="This is the problem title"
            sop="false"
            />
    </customresponse>
</problem>