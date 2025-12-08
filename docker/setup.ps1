# PowerShell setup script for Windows

Write-Host "🚀 Setting up Translation Management Service..." -ForegroundColor Green

# Check if .env exists
if (-not (Test-Path .env)) {
    Write-Host "📝 Creating .env file from .env.example..." -ForegroundColor Yellow
    Copy-Item .env.example .env
}

# Install PHP dependencies
Write-Host "📦 Installing PHP dependencies..." -ForegroundColor Yellow
docker compose exec app composer install --no-interaction

# Generate application key
Write-Host "🔑 Generating application key..." -ForegroundColor Yellow
docker compose exec app php artisan key:generate

# Run migrations
Write-Host "🗄️  Running database migrations..." -ForegroundColor Yellow
docker compose exec app php artisan migrate --force

# Create storage link
Write-Host "🔗 Creating storage symlink..." -ForegroundColor Yellow
docker compose exec app php artisan storage:link

# Seed initial data (optional)
$seed = Read-Host "Do you want to seed initial data? (y/n)"
if ($seed -eq "y" -or $seed -eq "Y") {
    Write-Host "🌱 Seeding database..." -ForegroundColor Yellow
    docker compose exec app php artisan db:seed
}

Write-Host "✅ Setup complete!" -ForegroundColor Green
Write-Host ""
Write-Host "🌐 Application is available at: http://localhost:8080" -ForegroundColor Cyan
Write-Host "📊 MySQL is available at: localhost:3306" -ForegroundColor Cyan
Write-Host "🔴 Redis is available at: localhost:6379" -ForegroundColor Cyan
Write-Host ""
Write-Host "To start the containers: docker compose up -d" -ForegroundColor Yellow
Write-Host "To stop the containers: docker compose down" -ForegroundColor Yellow
Write-Host "To view logs: docker compose logs -f" -ForegroundColor Yellow

