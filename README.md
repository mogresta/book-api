## Requirements

- Docker (v27.5)
- Docker Compose

## Setup

1. Clone the repository
2. Run: 
```bash
docker compose -f docker/docker-compose.yml up -d --build
```
3. Install dependencies:
```bash
docker compose -f docker/docker-compose.yml exec books composer install
```
4. Create database schema:
```bash
docker compose -f docker/docker-compose.yml exec books php bin/console doctrine:schema:create
```
5. Generate fixtures:
```bash
docker compose -f docker/docker-compose.yml exec books php bin/console doctrine:fixtures:load
```
If for whatever reason you need to drop the schema:
```bash
docker compose -f docker/docker-compose.yml exec books php bin/console doctrine:schema:drop
```

## API Endpoints

- GET /api/books - List all books 
- GET /api/books?page=2&limit=10    # Page 2 with 10 items per page 
- GET /api/books?genre=Fiction      # Filter by genre with default pagination
- GET /api/books/{isbn} - Get book by ISBN
- POST /api/books - Create new book
- PUT /api/books/{isbn} - Update book
- DELETE /api/books/{isbn} - Delete book

### Request Body Example (POST/PUT)

```json
{
    "isbn": "9780123456789",
    "title": "Sample Book",
    "author": "John Doe",
    "publishedYear": 2023,
    "genre": "Fiction"
}
```

## Running Tests
1. Create test database:
```bash
docker compose -f docker/docker-compose.yml exec books php bin/console --env=test doctrine:database:create
docker compose -f docker/docker-compose.yml exec books php bin/console --env=test doctrine:schema:create
```
2. Run tests:
```bash
docker compose -f docker/docker-compose.yml exec books php bin/phpunit
```

## Possible Improvements outside the scope of the task
- API documentation i.e. using OpenAPI
- Caching
- Rate Limiting
- Auth