# Setup ————————————————————————————————————————————————————————————————————————————————————————————————————————————————
PROJECT_PREFIX=broadway-sensitive-serializer

# Static ———————————————————————————————————————————————————————————————————————————————————————————————————————————————
.DEFAULT_GOAL := help
PHP_IMAGE=$(PROJECT_PREFIX)-php
NODEJS_IMAGE=$(PROJECT_PREFIX)-nodejs
PROJECT_NAME=$(shell basename $$(pwd) | tr '[:upper:]' '[:lower:]')
PHP_USER=utente
WORKDIR=/var/www/app
PROJECT_TOOL=$(WORKDIR)/tools/bin/project/project
PROJECT_TOOL_RELATIVE=./tools/bin/project/project

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
up: ## Up of containers
	$(COMPOSE) up $$ARG

.PHONY: upd
upd: ## Up of containers in daemon mode
	$(COMPOSE) up -d $$ARG

.PHONY: build-php
build-php: ## PHP container build
	docker build -f $(PHP_DOCKERFILE) --tag $(PHP_IMAGE) $$ARG .

.PHONY: enter
enter: ## Enter into the PHP container as root
	$(COMPOSE_EXEC_PHP) /bin/zsh

.PHONY: enter-root
enter-root: ## Enter into the PHP container as root
	$(COMPOSE) exec -u root $(PHP_IMAGE) /bin/zsh

.PHONY: down
down: ## Down of containers
	$(COMPOSE) down $$ARG

.PHONY: purge
purge: ## Down of containers and cleaning of images and volumes
	$(COMPOSE) down --rmi=all --volumes --remove-orphans

.PHONY: log
log: ## Docker container logs
	$(COMPOSE) logs -f

.PHONY: ps
ps: ## Container list
	@$(COMPOSE) ps

.PHONY: compose
compose: ## Wrapper to docker compose
	@$(COMPOSE) $$ARG

# Commitlint commands ——————————————————————————————————————————————————————————————————————————————————————————————————
.PHONY: conventional
conventional: ## Call conventional commit to validate the latest commit message
	$(COMPOSE_RUN) -T $(NODEJS_IMAGE) commitlint -e --from=HEAD -V

# PHP commands —————————————————————————————————————————————————————————————————————————————————————————————————————————
.PHONY: composer
composer: ## Wrapper to composer. Ex: make composer ARG=update
	$(COMPOSE_EXEC_PHP) composer $$ARG

.PHONY: php-run
php-run: ## Executes commands inside the PHP container. Ex: make php-run ARG="php -v"
	$(COMPOSE_EXEC_PHP) $$ARG

# CS Fixer commands ————————————————————————————————————————————————————————————————————————————————————————————————————
.PHONY: coding-standard-fix
coding-standard-fix: ## Format files. Without parameters it formats everything. Ex: make coding-standard-fix ARG="./file.php"
	$(COMPOSE_EXEC_PHP) $(PROJECT_TOOL) coding-standard-fix $$ARG

.PHONY: coding-standard-check-staged
coding-standard-check-staged: ## Check the coding style of the files in git stage
	$(COMPOSE_EXEC_PHP_NO_PSEUSO_TTY) $(PROJECT_TOOL) coding-standard-check-staged

.PHONY: coding-standard-fix-staged
coding-standard-fix-staged: ## Fix coding style of files in git stage
	$(COMPOSE_EXEC_PHP_NO_PSEUSO_TTY) $(PROJECT_TOOL) coding-standard-fix-staged

# Test commands ————————————————————————————————————————————————————————————————————————————————————————————————————————
.PHONY: phpunit
phpunit: ## It runs the entire test suite. Or on a specific file: make phpunit ARG=./Test.php
	$(COMPOSE_EXEC_PHP_NO_PSEUSO_TTY) $(PROJECT_TOOL) phpunit

.PHONY: coverage
coverage: ## It runs the entire suite of tests and verifies the coverage
	$(COMPOSE_EXEC_PHP) $(PROJECT_TOOL) coverage

# Static analysis ——————————————————————————————————————————————————————————————————————————————————————————————————————
.PHONY: psalm
psalm: ## Performs static analysis on the whole project. Or on a specific file: make psalm ARG=./File.php
	$(COMPOSE_EXEC_PHP_NO_PSEUSO_TTY) $(PROJECT_TOOL) psalm $$ARG

.PHONY: psalm-taint
psalm-taint: ## Performs psalm-based security checks
	$(COMPOSE_EXEC_PHP_NO_PSEUSO_TTY) $(PROJECT_TOOL) psalm-taint $$ARG

# Dependencies vulnerabilities —————————————————————————————————————————————————————————————————————————————————————————
.PHONY: check-deps-vulnerabilities
check-deps-vulnerabilities: ## Check for dependency vulnerabilities
	$(COMPOSE_EXEC_PHP_NO_PSEUSO_TTY) $(PROJECT_TOOL) check-deps-vulnerabilities

# Deptrac ——————————————————————————————————————————————————————————————————————————————————————————————————————————————
#.PHONY: deptrac-table-all
#deptrac-table-all: ## Performs deptrac for checking architectural constraints
#	$(COMPOSE_EXEC_PHP_NO_PSEUSO_TTY) $(PROJECT_TOOL) deptrac-table-all $$ARG

# Docs —————————————————————————————————————————————————————————————————————————————————————————————————————————————————
.PHONY: build-docs
build-docs: ## Build documentation with sphinx
	$(COMPOSE_EXEC_PHP_NO_PSEUSO_TTY) $(PROJECT_TOOL) build-docs

# Project autopilot command ————————————————————————————————————————————————————————————————————————————————————————————
.PHONY: project
project: ## Wrapper to invoke the project tool inside the php container. Use make project ARG=shortlist for the list of operations
	if [ ! -f ${PROJECT_TOOL_RELATIVE} ]; then \
		$(COMPOSE_EXEC_PHP) composer install; \
    fi; \
	$(COMPOSE_EXEC_PHP_NO_PSEUSO_TTY) $(PROJECT_TOOL) $$ARG

# Help —————————————————————————————————————————————————————————————————————————————————————————————————————————————————
.PHONY: help
help: ## Show this help
	@grep -hE '^[A-Za-z0-9_ \-]*?:.*##.*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'