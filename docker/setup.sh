#!/bin/bash

echo "🚀 Setting up Translation Management Service..."

# Check if .env exists
if [ ! -f .env ]; then
    echo "📝 Creating .env file from .env.example..."
    cp .env.example .env
fi

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
docker compose exec app composer install --no-interaction

# Generate application key
echo "🔑 Generating application key..."
docker compose exec app php artisan key:generate

# Run migrations
echo "🗄️  Running database migrations..."
docker compose exec app php artisan migrate --force

# Create storage link
echo "🔗 Creating storage symlink..."
docker compose exec app php artisan storage:link

# Seed initial data (optional)
read -p "Do you want to seed initial data? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "🌱 Seeding database..."
    docker compose exec app php artisan db:seed
fi

echo "✅ Setup complete!"
echo ""
echo "🌐 Application is available at: http://localhost:8080"
echo "📊 MySQL is available at: localhost:3306"
echo "🔴 Redis is available at: localhost:6379"
echo ""
echo "To start the containers: docker compose up -d"
echo "To stop the containers: docker compose down"
echo "To view logs: docker compose logs -f"

