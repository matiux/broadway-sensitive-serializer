# Setup ————————————————————————————————————————————————————————————————————————————————————————————————————————————————
PROJECT_PREFIX=broadway-sensitive-serializer

# Static ———————————————————————————————————————————————————————————————————————————————————————————————————————————————
PHP_IMAGE=$(PROJECT_PREFIX)-php
NODEJS_IMAGE=$(PROJECT_PREFIX)-nodejs
PROJECT_NAME=$(shell basename $$(pwd) | tr '[:upper:]' '[:lower:]')
PHP_USER=utente
WORKDIR=/var/www/app
PROJECT_TOOL=$(WORKDIR)/tools/bin/project/project

# Docker conf ——————————————————————————————————————————————————————————————————————————————————————————————————————————
PHP_DOCKERFILE=./docker/php/Dockerfile

ifeq ($(wildcard ./docker/docker-compose.override.yml),)
	COMPOSE_OVERRIDE=
else
	COMPOSE_OVERRIDE=-f ./docker/docker-compose.override.yml
endif

COMPOSE=docker compose --file docker/docker-compose.yml $(COMPOSE_OVERRIDE) -p $(PROJECT_NAME)

COMPOSE_EXEC=$(COMPOSE) exec -u $(PHP_USER) -w $(WORKDIR)
COMPOSE_EXEC_PHP=$(COMPOSE_EXEC) $(PHP_IMAGE)
COMPOSE_EXEC_PHP_NO_PSEUSO_TTY=$(COMPOSE_EXEC) -T $(PHP_IMAGE)

COMPOSE_RUN=$(COMPOSE) run --rm

# Docker commands ——————————————————————————————————————————————————————————————————————————————————————————————————————
.PHONY: up
up:
	$(COMPOSE) up $$ARG

.PHONY: upd
upd:
	$(COMPOSE) up -d $$ARG

.PHONY: build-php
build-php:
	docker build -f $(PHP_DOCKERFILE) --tag $(PHP_IMAGE) $$ARG .

.PHONY: enter
enter:
	$(COMPOSE_EXEC_PHP) /bin/zsh

.PHONY: enter-root
enter-root:
	$(COMPOSE) exec -u root $(PHP_IMAGE) /bin/zsh

.PHONY: down
down:
	$(COMPOSE) down $$ARG

.PHONY: purge
purge:
	$(COMPOSE) down --rmi=all --volumes --remove-orphans

.PHONY: log
log:
	$(COMPOSE) logs -f

.PHONY: ps
ps:
	@$(COMPOSE) ps

.PHONY: compose
compose:
	@$(COMPOSE) $$ARG

# Commitlint commands ——————————————————————————————————————————————————————————————————————————————————————————————————

.PHONY: conventional
conventional:
	$(COMPOSE_RUN) -T $(NODEJS_IMAGE) commitlint -e --from=HEAD -V

# PHP commands —————————————————————————————————————————————————————————————————————————————————————————————————————————

.PHONY: composer
composer:
	$(COMPOSE_EXEC_PHP) composer $$ARG

.PHONY: php-run
php-run:
	$(COMPOSE_EXEC_PHP) $$ARG

# CS Fixer commands ————————————————————————————————————————————————————————————————————————————————————————————————————

.PHONY: coding-standard-fix
coding-standard-fix:
	$(COMPOSE_EXEC_PHP) $(PROJECT_TOOL) coding-standard-fix $$ARG

.PHONY: coding-standard-check-staged
coding-standard-check-staged:
	$(COMPOSE_EXEC_PHP_NO_PSEUSO_TTY) $(PROJECT_TOOL) coding-standard-check-staged

.PHONY: coding-standard-fix-staged
coding-standard-fix-staged:
	$(COMPOSE_EXEC_PHP_NO_PSEUSO_TTY) $(PROJECT_TOOL) coding-standard-fix-staged

# Test commands ————————————————————————————————————————————————————————————————————————————————————————————————————————

.PHONY: phpunit
phpunit:
	$(COMPOSE_EXEC_PHP_NO_PSEUSO_TTY) $(PROJECT_TOOL) phpunit

.PHONY: coverage
coverage:
	$(COMPOSE_EXEC_PHP) $(PROJECT_TOOL) coverage

# Static analysis ——————————————————————————————————————————————————————————————————————————————————————————————————————

.PHONY: psalm
psalm:
	$(COMPOSE_EXEC_PHP_NO_PSEUSO_TTY) $(PROJECT_TOOL) psalm $$ARG

.PHONY: psalm-taint
psalm-taint:
	$(COMPOSE_EXEC_PHP_NO_PSEUSO_TTY) $(PROJECT_TOOL) psalm-taint $$ARG

# Docs —————————————————————————————————————————————————————————————————————————————————————————————————————————————————

.PHONY: build-docs
build-docs:
	$(COMPOSE_EXEC_PHP_NO_PSEUSO_TTY) $(PROJECT_TOOL) build-docs

# Project autopilot command ————————————————————————————————————————————————————————————————————————————————————————————

.PHONY: project
project:
	$(COMPOSE_EXEC_PHP_NO_PSEUSO_TTY) $(PROJECT_TOOL) $$ARG