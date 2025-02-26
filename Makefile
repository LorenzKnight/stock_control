.PHONY: start down migrate

start:
	docker-compose up -d
	@echo "Waiting for postgres..."
	sleep 5
	$(MAKE) migrate
	@echo "Local IP Address: http://$$(ifconfig | grep -Eo 'inet (addr:)?([0-9]*\.){3}[0-9]*' | grep -Eo '([0-9]*\.){3}[0-9]*' | grep -v '127.0.0.1' | head -n 1):8889/"

down:
	docker-compose down

migrate:
	cat ./.conf/postgresql/postgresql-dump.sql | docker-compose exec -T postgres-db psql --user=admin stock_control_db