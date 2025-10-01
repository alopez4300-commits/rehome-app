# Makefile
.DEFAULT_GOAL := help
.PHONY: help setup up down logs clean restart status

help: ## Show this help message
	@echo "🚀 ReHome v2 - Development Commands"
	@echo "=================================="
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

setup: ## Install dependencies & build assets
	@echo "📦 Installing dependencies..."
	composer install
	cd frontend && npm install
	@echo "✅ Setup complete!"

up: ## Start all containers
	@echo "🐳 Starting containers..."
	docker compose up -d
	@echo "✅ Containers started!"

down: ## Stop all containers
	@echo "🛑 Stopping containers..."
	docker compose down
	@echo "✅ Containers stopped!"

logs: ## Tail logs for key services
	@echo "📜 Tailing logs (Ctrl+C to exit)..."
	docker compose logs -f app nginx

migrate: ## Run database migrations
	@echo "🗄️  Running migrations..."
	docker compose exec app php artisan migrate --force
	@echo "✅ Migrations complete!"

seed: ## Seed database with test data
	@echo "🌱 Seeding database..."
	docker compose exec app php artisan db:seed --force
	@echo "✅ Database seeded!"

admin: ## Create admin user
	@echo "👤 Creating admin user..."
	docker compose exec app php artisan make:filament-user
	@echo "✅ Admin user created!"

status: ## Show container status
	@echo "📊 Container Status:"
	@docker compose ps

clean: ## Clean up containers and volumes
	@echo "🧹 Cleaning up..."
	docker compose down -v --remove-orphans
	docker system prune -f

dev: ## Start development server (Codespace compatible)
	@echo "🚀 Starting development server..."
	php artisan serve --host=0.0.0.0 --port=8000

fresh: ## Fresh setup with migrations and admin user
	@echo "🔄 Fresh setup..."
	composer install
	php artisan key:generate
	php artisan migrate --force
	php artisan make:filament-user
	@echo "✅ Fresh setup complete!"
