SHELL := /usr/bin/env bash
COMPOSE := docker compose -f docker/docker-compose.yml --env-file .env

ifneq (,$(wildcard .env))
    include .env
    export
endif

.PHONY: up down restart ps logs \
        tools frontend dev \
        sh \
        db-shell db-import db-structure db-data db-pictures db-truncate \
        test pre-commit

up:
	$(COMPOSE) up -d

down:
	$(COMPOSE) down

restart:
	$(COMPOSE) down
	$(COMPOSE) up -d

ps:
	$(COMPOSE) ps

logs:
	$(COMPOSE) logs -f --tail=200

tools:
	$(COMPOSE) --profile tools up -d

frontend:
	$(COMPOSE) --profile frontend up -d

dev: up frontend

sh:
	$(COMPOSE) exec php bash

db-shell:
	$(COMPOSE) exec mariadb mariadb -u root -p"$(DB_ROOT_PASSWORD)"

db-import:
	@if [[ -z "$$SQL" ]]; then echo "Usage: make db-import SQL=sql/file.sql"; exit 1; fi
	$(COMPOSE) exec -T mariadb mariadb -u root -p"$(DB_ROOT_PASSWORD)" < "$$SQL"

db-structure:
	$(COMPOSE) exec -T mariadb mariadb -u root -p"$(DB_ROOT_PASSWORD)" < sql/project_structure.sql

db-data:
	$(COMPOSE) exec -T mariadb mariadb -u root -p"$(DB_ROOT_PASSWORD)" < sql/project_data_202602022233.sql

db-pictures:
	$(COMPOSE) exec -T mariadb mariadb -u root -p"$(DB_ROOT_PASSWORD)" < sql/pictures_data_202602032052.sql

db-truncate:
	$(COMPOSE) exec -T mariadb mariadb -u root -p"$(DB_ROOT_PASSWORD)" < sql/truncate_photos.sql

test:
	$(COMPOSE) exec -T php vendor/bin/phpunit

pre-commit:
	composer run-script pre-commit
	npm run test
