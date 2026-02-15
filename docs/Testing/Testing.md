## PHP
There are 3 PHP test suites provided. CodeSniffer, PHPStan (level 9), and PHPUnit, which can be run from the following composer commands:
* `phpcs` - CodeSniffer to ensure that the code is formatted to PSR12 standards.
* `phpstan` - PHPStan to perform static analysis of the code, ensuring that function inputs and outputs etc will perform reliably.
* `test` - PHPUnit to run the unit tests for the application.
* `pre-commit` - This command will run all three of the above.
## TypeScript
TypeScript tests are written using Jest.
## Docker
To make things easier, there is a `pre-commit` command in the docker Makefile that will run all 3 PHP test suites and the TypeScript tests in one go, thus ensuring that the code meets the requirements of the project.
## Pipeline
There is a CI pipeline that will also run all of the tests when you push your commits to GitLab, thus ensuring that the code in the remote repository always meets the requirements of the project.
