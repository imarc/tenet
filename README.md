# Project Name

A brief description of the project.

# Changelog

The running / master changelog for this package is available [here](/).

You can view the changelog relative to your working copy by executing:

	git log --simplify-by-decoration --oneline --tags

# Documentation

Documentation for this project is available in the `docs` directory and should be maintained with the project under version control.  Additionally, iMarc uses [sage](https://github.com/dotink/sage) to generate API docs under `docs/api`.  You can install sage on your local system and execute the following to update the docs:

	php <path/to/sage.php> src docs/api
