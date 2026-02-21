# Docker (local development)
This project ships with a Docker-based development environment so everyone can run the same stack locally.
The Docker setup lives in [`/docker`](../../docker) and is driven via the root [`Makefile`](../../Makefile) (recommended).

## Requirements
- Docker Engine + Docker Compose v2 (`docker compose`)
- (Optional) GNU Make (`make`)

## Quick start
1. Create your env file:
    - Copy `.env.example` to `.env`
    - Fill in any required values (ports, DB credentials, etc.)

2. Build and start the containers:
    ```bash
    make build
    make up
    ```

3. Open the application in your browser:
    - App: `http://localhost:<APP_PORT>` (defaults to `8082` if not set)
    - phpMyAdmin (tools profile): `http://localhost:<PHPMYADMIN_PORT>` (defaults to `8081`)

## Common commands
The `Makefile` wraps Docker Compose so you don’t need to remember flags/paths.

### Containers
```bash
make up # start the main stack (PHP, Nginx, MariaDB) 
make down # stop the stack 
make restart # restart everything 
make ps # show container status 
make logs # follow logs 
make sh # shell into the PHP container
```

### Optional profiles
Some services are started only when needed:
```bash 
make tools # starts tools profile (phpMyAdmin) 
make frontend # starts frontend profile (Node dev server / asset watcher) 
make dev # convenience: main stack + frontend
```

## Database
### Connect to MariaDB
```bash
make db-shell
```
This uses the root password from your `.env` (`DB_ROOT_PASSWORD`).

### Import SQL files
There are convenience targets for importing SQL:
```bash 
make db-import SQL=sql/file.sql 
make db-structure 
make db-data 
make db-pictures 
make db-truncate
```
Notes:
- These commands run **inside** the MariaDB container.
- If you add new SQL snapshots, prefer adding a new `make` target (so the workflow stays discoverable).

## Running tests
Run PHPUnit inside the PHP container:
```bash 
make test
```
There is also a pre-commit helper
```bash 
make pre-commit
```

## Ports and services (defaults)
- **Nginx**: `APP_PORT` → container port `80` (default `8082`)
- **MariaDB**: `DB_PORT` → container port `3306` (default `3306`)
- **phpMyAdmin** (tools profile): `PHPMYADMIN_PORT` → container port `80` (default `8081`)

All of these can be changed via `.env`.

## Local pictures mount (important)
The Docker Compose setup mounts a host pictures directory into:
- `/var/www/html/public/resources/pictures` (read-only)

If you don’t have that directory on your machine, containers may fail to start.

Recommended approaches:
- **Best**: adjust the bind-mount in `docker/docker-compose.yml` to point at a directory that exists on your machine.
- **Team-friendly**: switch the bind-mount to use an environment variable (e.g. `PICTURES_DIR`) so each developer can configure it in `.env`.

## Where the configuration lives
- Docker Compose: [`docker/docker-compose.yml`](../../docker/docker-compose.yml)
- PHP image: [`docker/php/Dockerfile`](../../docker/php/Dockerfile)
- PHP config: [`docker/php/php.ini`](../../docker/php/php.ini)
- Nginx vhost: [`docker/nginx/default.conf`](../../docker/nginx/default.conf)

## Optional: global Make shortcuts (Linux / WSL)
If you are using Linux (or WSL), there is another project on GitLab that can help you run common Docker commands without typing the full project path:
<https://gitlab.com/markbellingham/projects>
