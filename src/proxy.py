from flask import Flask, Response, render_template
import requests

app = Flask(__name__)
PROXY_PATH = "https://h5p.org/"  # TODO: Change this to be configurable
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
]


def pass_headers(_from, _to):  # TODO: Find another way to do this
    for x in _from:
        if x not in ALLOWED_HEADERS:
            continue
        _to[x] = _from[x]


@app.route("/")
def index():
    return "Hello, world"


@app.route("/embed/<int:embed_id>")
def embed(embed_id: int):
    res = requests.get(PROXY_PATH + "h5p/embed/" + str(embed_id))
    if res.status_code != 200:
        return res.text, res.status_code

    body = res.text
    return render_template("proxy.html", body=body)


@ app.route("/<path:path>")
def proxy(path: str):
    proxy_res = requests.get(PROXY_PATH + path)

    # TODO: This is not what we should do
    response = app.make_response(proxy_res.content)
    print(response)

    pass_headers(proxy_res.headers, response.headers)
    return response


app.run(port=5001, debug=True)
