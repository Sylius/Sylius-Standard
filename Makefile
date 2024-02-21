.PHONY: run

DOCKER_USER ?= "$(shell id -u):$(shell id -g)"
ENV ?= "dev"

init:
	@if [ ! -e compose.override.yml ]; then \
		cp compose.override.dist.yml compose.override.yml; \
	fi
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) docker-compose run --rm php composer install --no-interaction --no-scripts
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) docker-compose run --rm nodejs
	make install
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) docker-compose up -d

run:
	make up

debug:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) docker-compose -f compose.yml -f compose.override.yml -f compose.debug.yml up -d

up:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) docker-compose up -d

down:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) docker-compose down

install:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) docker-compose run --rm php bin/console sylius:install -s default -n

clean:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) docker-compose down -v

php-shell:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) docker-compose exec php sh

node-shell:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) docker-compose run --rm -i nodejs sh

yarn-watch:
	@ENV=$(ENV) DOCKER_USER=$(DOCKER_USER) docker-compose run --rm -i nodejs "yarn watch"
