from flask import Flask, Response, render_template, request
import requests
import urllib
import base64

app = Flask(__name__)
PROXY_PATH = "https://h5p.org/"  # TODO: Change this to be configurable
EXTERNAL_URL = "http://localhost:5001/"
ALLOWED_HEADERS = [
    # Cloudflare headers
    "Cf-Bgj",
    "CF-Cache-Status",
    "CF-RAY",
    "cf-request-id",

    "Age",
    "Cache-Control",
    "Connection",
    "Content-Type",
    "Date",
    "ETag",
    "Expires",
    "Last-Modified",
    "Server",
    "Set-Cookie",

    "Connection",
    "Accept",
    "Accept-Encoding",
    "Accept-Language",
    "Cookie",
    "User-Agent",
]


def b64e(x):
    return str(base64.b64encode(bytes(x, "utf-8")), "utf-8")


def b64d(x):
    return str(base64.b64decode(bytes(x + "===", "utf-8")), "utf-8")


def pass_headers(_from, _to):  # TODO: Find another way to do this
    for x in _from:
        if x not in ALLOWED_HEADERS:
            continue
        _to[x] = _from[x]


def create_headers_from(_headers):
    return {
        header[0]: header[1]
        for header in _headers if header[0] in ALLOWED_HEADERS
    }


@app.route("/")
def index():
    return "Hello, world"


@app.route("/embed")
def embed():
    path = request.args.get("website")
    if not path:
        return "Website not specified", 404

    path = b64d(path)

    res = requests.get(path, headers=create_headers_from(request.headers))
    if res.status_code != 200:
        return res.text, res.status_code

    parsed = urllib.parse.urlparse(path)
    base_path = f"{parsed.scheme}://{parsed.netloc}/"
    redirect_path = "/" + b64e(base_path) + "/"

    body = res.text\
        .replace('src="/', 'src="' + redirect_path)\
        .replace('href="/', 'href="' + redirect_path)

    response = app.make_response(render_template("proxy.html", body=body))

    # TODO: Code duplication?
    pass_headers(res.headers, response.headers)
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


@app.route("/<path:path>")
def proxy(path: str):
    base_path, *other_parts = path.split("/")
    rest_of_path = "/".join(other_parts)

    try:
        base_path = b64d(base_path)
    except Exception:  # TODO: Be more specific
        return "Can't decode hex", 400

    proxy_res = requests.get(base_path + rest_of_path, headers=create_headers_from(request.headers))

    # TODO: This is not what we should do
    response = app.make_response(proxy_res.content)

    pass_headers(proxy_res.headers, response.headers)
    response.headers["Access-Control-Allow-Origin"] = "*"
    response.headers["Cache-Control"] = "public, max-age=2600000"

    return response


app.run(port=5001, debug=True, threaded=True)
