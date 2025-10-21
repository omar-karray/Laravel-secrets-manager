# Documentation Deployment

The docs in this repository are powered by [MkDocs](https://www.mkdocs.org/) with the Material theme. Follow the steps below to build and publish the site to GitHub Pages.

## Local preview

1. Install dependencies (Python 3.8+ required):

    ```bash
    pip install mkdocs mkdocs-material
    ```

2. Serve the documentation locally:

    ```bash
    mkdocs serve
    ```

    Visit <http://127.0.0.1:8000> to preview the site with live reload.

## Build static assets

```bash
mkdocs build
```

The generated HTML lives in the `site/` directory (ignored from git by default).

## Deploy to GitHub Pages

We recommend the `mkdocs gh-deploy` command, which builds the site and pushes it to the `gh-pages` branch automatically:

```bash
mkdocs gh-deploy --clean
```

This command requires push access to the repository. After the first deployment, enable GitHub Pages under **Settings → Pages** and choose the `gh-pages` branch.

### GitHub Actions workflow (optional)

You can automate deployments by adding a workflow similar to:

```yaml
name: Deploy Docs

on:
  push:
    branches:
      - main
    paths:
      - 'mkdocs.yml'
      - 'docs/**'

jobs:
  build-and-deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-python@v5
        with:
          python-version: '3.x'
      - run: pip install mkdocs mkdocs-material
      - run: mkdocs gh-deploy --force
```

This keeps GitHub Pages up to date whenever documentation changes land on `main`.
