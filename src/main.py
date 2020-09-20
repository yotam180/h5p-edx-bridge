from flask import Flask, Response, render_template, request, redirect, send_from_directory
import requests
import urllib

from http_stuff import ALLOWED_HEADERS
from b64 import b64e, b64d

"""
How does this work?

This hides H5P embeds behind a proxy. It does two things 
- Serve the main embed file, and append a script to it to communicate with EdX
- Serve as a proxy for other asset files (scripts/images/css/etc...)

The main file is served under /embed?website=aaaaaaaa where aaaaaaaa is the embed URL encoded as base64
The file will be retuend with a concatenated script, and also with modified paths (continue reading)

Other asset files are served in the following way:

/aaaaaaaa/sites/h5p/script.js will return the file /sites/h5p/script.js from the website aaaaaaaa (a being the website, base64 encoded)
For example aaaaaaaa might be "https://h5p.org/" in base64.

If the URL is not prepended with the base64 base path, we attempt to read the base path from the referer field (remember the ?website= parameter?)
and we return a redirect to the URL with the base path prepended.

Also, for the main embed file, every path we recognize as starting with "/", we convert it to {full url}/aaaaaaaaa/<rest of path> to avoid
unnecessary redirections.

"""

app = Flask(__name__)
EXTERNAL_URL = "http://localhost:5001/"  # TODO: This should come from an environment variable


def headers_requests_to_flask(_from, _to):
    """
    Takes all headers from the _from headers set (returned from a requests call)
    and adds them into the _to headers set that is to be returned to whoever called
    our Flask request.
    """
    for x in _from:
        if x not in ALLOWED_HEADERS:
            continue
        _to[x] = _from[x]


def headers_flask_to_requests(_headers):
    """
    Takes the retrieved Flask headers from the incoming request, and converts them into a
    dictionary to pass them into a requests dispatch.
    """
    return {
        header[0]: header[1]
        for header in _headers if header[0] in ALLOWED_HEADERS
    }


@app.route("/")
def index():
    return render_template("index.html", EXTERNAL_URL=EXTERNAL_URL)


@app.route("/embed")
def embed():
    path = request.args.get("website")
    if not path:
        return "Website not specified", 404

    path = b64d(path)
    res = requests.get(path, headers=headers_flask_to_requests(request.headers))
    if res.status_code != 200:
        return res.text, res.status_code

    parsed = urllib.parse.urlparse(path)
    base_path = f"{parsed.scheme}://{parsed.netloc}/"
    redirect_path = "/" + b64e(base_path) + "/"

    body = res.text\
        .replace('src="/', 'src="' + redirect_path)\
        .replace('href="/', 'href="' + redirect_path)

    response = app.make_response(render_template("proxy.html", body=body, **request.args))

    # TODO: Code duplication?
    headers_requests_to_flask(res.headers, response.headers)
    response.headers["Access-Control-Allow-Origin"] = "*"
    # TODO: Put here sth else in production
    response.headers["Cache-Control"] = "no-cache"

    return response


@app.route("/template")
def template_generator():
    # TODO: Validate that args has website, failure, success, etc.
    response = app.make_response(render_template("template.html", EXTERNAL_URL=EXTERNAL_URL, **request.args))
    response.headers["Content-Type"] = "text/plain"
    return response


@app.route("/camera_microphone.js")
def static_files():
    return send_from_directory("templates/", "camera_microphone.js")


def try_proxy_without_base64():
    referer = request.headers.get("Referer", "")
    qs = urllib.parse.parse_qs(urllib.parse.urlparse(referer).query)
    if "website" in qs:
        website = b64d("".join(qs.get("website")))
        parsed = urllib.parse.urlparse(website)
        base_path = f"{parsed.scheme}://{parsed.netloc}/"
        return redirect("/" + b64e(base_path) + request.path)

    return "Can't decode hex", 400


@app.route("/<path:path>")
def proxy(path: str):
    base_path, *other_parts = path.split("/")
    rest_of_path = "/".join(other_parts)

    try:
        base_path = b64d(base_path)
    except Exception:  # TODO: Be more specific
        return try_proxy_without_base64()

    proxy_res = requests.get(base_path + rest_of_path, headers=headers_flask_to_requests(request.headers))

    response = app.make_response(proxy_res.content)

    headers_requests_to_flask(proxy_res.headers, response.headers)
    response.headers["Access-Control-Allow-Origin"] = "*"
    response.headers["Cache-Control"] = "public, max-age=2600000"

    return response


if __name__ == "__main__":
    app.run(port=5001, debug=True, threaded=True)
