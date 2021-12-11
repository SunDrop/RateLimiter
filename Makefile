docker_compose_file=docker/docker-compose.yml

.PHONY: help

help:
	@echo "\033[32mmake up \033[0m- up all containers"

logs:
	# Show (and watch) logs of services running
	docker-compose -f $(docker_compose_file) logs -f

up:
	@echo "\033[32mStarting containers...\033[0m"
	@docker-compose -f $(docker_compose_file) up -d

down:
	@echo "\033[32mStop containers...\033[0m"
	@docker-compose -f $(docker_compose_file) down -v --remove-orphans;

rebuild:
	@echo "\033[32mRebuild containers...\033[0m"
	@docker-compose -f $(docker_compose_file) build --force-rm --no-cache;


php:
	@echo "\033[32mEntering into php container...\033[0m"
	@docker-compose -f $(docker_compose_file) exec php bash

redis:
	@echo "\033[32mEntering into redis container...\033[0m"
	@docker-compose -f $(docker_compose_file) exec redis bash
