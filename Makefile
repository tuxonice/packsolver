.PHONY: docker-build docker-up docker-down docker-shell docker-exec docker-logs docker-test docker-cs docker-cs-fix

# Docker commands
docker-build:
	docker-compose build

docker-up:
	docker-compose up -d

docker-down:
	docker-compose down

docker-shell:
	docker-compose exec app sh

docker-exec:
	docker-compose exec app $(cmd)

docker-logs:
	docker-compose logs -f app

# Application commands
docker-test:
	docker-compose exec app composer test

docker-cs:
	docker-compose exec app composer cs

docker-cs-fix:
	docker-compose exec app composer cs-fix

# Help command
help:
	@echo "Available commands:"
	@echo "  docker-build    Build Docker containers"
	@echo "  docker-up       Start Docker containers"
	@echo "  docker-down     Stop Docker containers"
	@echo "  docker-shell    Open shell in app container"
	@echo "  docker-exec     Execute command in app container (use with cmd=...)"
	@echo "  docker-logs     View app container logs"
	@echo "  docker-test     Run tests in Docker"
	@echo "  docker-cs       Run code style check in Docker"
	@echo "  docker-cs-fix   Fix code style in Docker"
