## Translation Management API

API for managing locale-based translations with token auth, caching, and export support.

## Tech Stack
- PHP 8.2+, Laravel
- MySQL 8 (storage)
- Redis 7 (cache/queue)
- Docker & docker-compose (nginx, php-fpm, mysql, redis)
- PHPUnit for tests

## Prerequisites
- Docker & docker-compose installed
- Make sure ports are free: 8080 (nginx), 3308 (MySQL), 6379 (Redis)

## Setup & Run
```bash
# 1) Copy env
cp .env.example .env

# 2) Build & start
docker-compose up -d --build

# 3) Install composer deps (inside app container)
docker exec translation-management-app composer install

# 4) Generate app key
docker exec translation-management-app php artisan key:generate

# 5) Run migrations
docker exec translation-management-app php artisan migrate

# 6) (Optional) Seed sample translations
docker exec translation-management-app php artisan translations:seed --count=100000 --batch=1000
```

App will be available at `http://localhost:8080`.

## Authentication
Bearer token via custom token service.
```http
POST /api/v1/auth/token
{
  "email": "user@example.com",
  "password": "secret"
}
```
Response: `{ "data": { "token": "<plain-token>" } }`

Use header: `Authorization: Bearer <plain-token>`

## API Endpoints (v1)
Base path: `/api/v1`

- `POST /auth/token` — issue token

Translations (protected by `api.token` middleware):
- `POST /translations` — create translation
- `GET /translations/{translation}` — get translation
- `PUT /translations/{translation}` — update translation
- `DELETE /translations/{translation}` — delete translation
- `GET /translations/search` — search translations (supports filters)
- `GET /translations/export/locale/{locale}` — export translations by locale

## Response Shape (Translation)
```json
{
  "id": 1009,
  "locale_id": 8,
  "key": "test_en222",
  "value": "this is test es en",
  "created_at": "2025-12-10T07:34:41Z",
  "updated_at": "2025-12-10T07:34:41Z",
  "locale": { "id": 8, "code": "en", "name": "English" },
  "tags": [
    { "id": 3, "name": "mobile", "description": "Mobile" }
  ]
}
```

## Running Tests
```bash
# Entire suite
docker exec translation-management-app php artisan test

# Specific class
docker exec translation-management-app php artisan test --filter TranslationControllerStoreTest
```

## Notes
- Caching: translations and search use Redis cache tags; cache invalidates on create/update/delete.
- Export: `exportByLocale` streams via cursor to handle millions of records efficiently.
- Throttling: API group uses `throttle:api`.***
