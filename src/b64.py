import base64


def b64e(x):
    return str(base64.b64encode(bytes(x, "utf-8")), "utf-8")


def b64d(x):
    return str(base64.b64decode(bytes(x + "===", "utf-8")), "utf-8")
