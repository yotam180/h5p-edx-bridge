# [H5P to OpenEDX Bridge](https://h5p-edx-2.ey.r.appspot.com/)

This helps people connect activities from [H5P](https://h5p.org) to [OpenEDX](https://open.edx.org/)

## How does this work?

See `src/main.py` for documentation.

## TODO

1. We might be able to remove the server from the architecture if we just
hook the XMLHttpRequests to the H5P embeds (which is way better...)

## Deployment to App Engine

Run `gclouud app deploy` from the `src/` folder.
