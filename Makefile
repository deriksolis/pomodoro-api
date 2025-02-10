setup:
	@echo "Setting up dev env.."
	docker-compose up -d --build
	@echo "API is running at http://localhost:8000"

up:
	docker-compose up -d

down:
	docker-compose down

restart:
	docker-compose down && docker-compose up -d

hard-restart:
	docker-compose down --rmi all --volumes --remove-orphans
	rm -rf docker/mysql/data
	docker-compose up -d --build

logs:
	docker-compose logs -f

app-shell:
	docker exec -it pomodoro-api bash

db-shell:
	docker exec -it mysql_db mysql -u root -p

test:
	@docker exec -it pomodoro-api php vendor/bin/phpunit --testdox

