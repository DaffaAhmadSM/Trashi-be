.PHONY: compose

compose:
	@echo "Checking .env file..."
	@test -f .env || (cp .env.example .env && echo "Created .env from .env.example. Please update your ports!")

	@echo "Building Docker images..."
	docker compose build

	@echo "Installing Composer packages..."
	docker run --rm -it --volume $$(pwd):/app trashi-be composer install

	@echo "Generating application key..."
	docker run --rm -it --volume $$(pwd):/app trashi-be php artisan key:generate

	@echo "Installing and configuring Laravel Octane..."
	docker run --rm -it --volume $$(pwd):/app trashi-be composer require laravel/octane
	docker run --rm -it --volume $$(pwd):/app trashi-be php artisan octane:install --server=frankenphp

	@echo "Starting the Laravel application..."
	docker compose up -d

	@echo "Running database migrations..."
	docker compose exec app php artisan migrate --seed

	@echo "linking storage..."
	docker compose exec app php artisan storage:link

	@echo "Checking container status..."
	docker compose ps

	@echo "\nSetup complete! Surf 127.0.0.1 (at your APP_EXPOSED_PORT) in your web browser."
