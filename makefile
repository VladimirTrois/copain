# Executables (local)
DOCKER_COMP = docker compose

# Docker containers
PHP_CONT = $(DOCKER_COMP) exec php

# Executables
PHP      = $(PHP_CONT) php
COMPOSER = $(PHP_CONT) composer
SYMFONY  = $(PHP) bin/console

# Misc
.DEFAULT_GOAL = help
.PHONY        : help build up start down logs sh bash test rebuild composer vendor sf cc dropDB createDB migrateDB loadDB reloadDB newMigration rebootDB xdebug-log php-cs

## —— 🎵 🐳 The Symfony Docker Makefile 🐳 🎵 ——————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9\./_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Docker 🐳 ————————————————————————————————————————————————————————————————
build: ## Builds the Docker images
	@$(DOCKER_COMP) build --pull --no-cache

up: ## Start the docker hub in detached mode (no logs)
	@$(DOCKER_COMP) up -d --wait 

start: build up ## Build and start the containers

down: ## Stop the docker hub
	@$(DOCKER_COMP) down --remove-orphans

rebuild: down build up ## Tear down and rebuild everything

logs: ## Show live logs
	@$(DOCKER_COMP) logs --tail=0 --follow

sh: ## Connect to the FrankenPHP container
	@$(PHP_CONT) sh

bash: ## Connect to the FrankenPHP container via bash so up and down arrows go to previous commands
	@$(PHP_CONT) bash

test: ## Start tests with phpunit, pass the parameter "c=" to add options to phpunit, example: make test c="--group e2e --stop-on-failure"
	@$(DOCKER_COMP) exec -e APP_ENV=test php bin/phpunit "$(or $(c),)"


## —— Composer 🧙 ——————————————————————————————————————————————————————————————
composer: ## Run composer, pass the parameter "c=" to run a given command, example: make composer c='req symfony/orm-pack'
	@$(COMPOSER) "$(or $(c),list)"

vendor: ## Install vendors according to the current composer.lock file
	@$(COMPOSER) install --prefer-dist --no-dev --no-progress --no-scripts --no-interaction

## —— Symfony 🎵 ———————————————————————————————————————————————————————————————
sf: ## List all Symfony commands or pass the parameter "c=" to run a given command, example: make sf c=about
	@$(SYMFONY) "$(or $(c),list)"

cc: ## Clear the cache
	$(SYMFONY) c:c 

## —— Database ———————————————————————————————————————————————————————————————
dropDB: ## Drop the database
	$(SYMFONY) doctrine:database:drop --force

createDB: ## Create the database
	$(SYMFONY) doctrine:database:create

migrateDB: ## Migrate the database
	$(SYMFONY) doctrine:migrations:migrate --no-interaction

loadDB: ## Load fixtures or pass the parameter "c=" to run a given command, example: make loadDB c="--append"
	@$(SYMFONY) doctrine:fixtures:load "$(or $(c),)"

reloadDB: ## Reload fixtures no interaction
	@$(SYMFONY) doctrine:fixtures:load --no-interaction

newMigration: ## Create a new migration
	$(SYMFONY) make:migration

rebootDB: dropDB createDB migrateDB loadDB ## Drop, create, migrate and load

## —— Debug ————————————————————————————————————————————————————————————————————
xdebug-log: ## Show xdebug logs
	@$(PHP_CONT) tail -f /tmp/xdebug.log

## —— Code Style  ——————————————————————————————————————————————————————————————
php-cs: ## Run php-cs-fixer list or pass the parameter "c=" to run a given command, example: make php-cs c='fix --dry-run'
	@$(PHP_CONT) vendor/bin/php-cs-fixer "$(or $(c),list)"