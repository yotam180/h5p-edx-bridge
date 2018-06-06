<problem>
    <customresponse cfn="check_function">
        <script type="loncapa/python">
<![CDATA[

import json
def check_function(e, ans):
    response = json.loads(ans)
    return {"ok": True, "msg": response["answer"]}

]]>
        </script>

        <jsinput
            gradefn="window.getGrade"
            get_statefn="window.getState"
            set_statefn="window.setState"
            initial_state="false"
            width="100%"
            height="400"
            html_file="http://localhost:8080/activity?a=251488"
            title="This is the problem title"
            sop="false"
            />
    </customresponse>
</problem>