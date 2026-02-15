The application mostly follows DDD structure.
## Top level
`/build`
Contains webpack configuration files

`/config`
Contains things like passwords and application secrets

`/coverage`
Used by PHPStan to show on GitLab how much code is covered by tests

`/docker`
Docker configuration files. There is also a `Makefile` at the project root that contains shortcuts for common commands.

`/docs`
Documentation

`/public`
The webserver root

`/screenshots`
Images of the application used by the main `README.md` file.

`/sql`
SQL setup files

`/src`
PHP source files

`/templates`
Twig template files for front-end HTML markup

`/tests`
Unit tests for both PHP and TypeScript

`/web`
CSS and TypeScript files