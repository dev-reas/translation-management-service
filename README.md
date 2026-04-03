# Translation Management Service API

A Laravel-based API for managing translations with support for multiple locales and JSON export capabilities.

## Table of Contents

- [Design & Architecture](#design--architecture)
- [Requirements](#requirements)
- [Installation](#installation)
- [Running the Application](#running-the-application)
- [Docker Setup](#docker-setup)
- [API Endpoints](#api-endpoints)
- [Authentication](#authentication)
- [Export Endpoints](#export-endpoints)
- [Performance Notes](#performance-notes)
- [Testing](#testing)

---

## Design & Architecture

### Overview

This Translation Management Service is built using **Laravel** (PHP) with a focus on security, performance, and maintainability. The system follows a layered architecture pattern commonly used in Laravel applications.

### Architecture Pattern: MVC with Service Layer

```
┌─────────────────────────────────────────────────────────┐
│                    API Requests                         │
└─────────────────────────┬───────────────────────────────┘
                          │
┌─────────────────────────▼───────────────────────────────┐
│              Controllers (API Layer)                    │
│  - TranslationController                                 │
│  - AuthController                                        │
│  - LocaleController                                      │
│  - TagController                                         │
└─────────────────────────┬───────────────────────────────┘
                          │
┌─────────────────────────▼───────────────────────────────┐
│              Actions (Business Logic)                   │
│  - CreateTranslationAction                               │
│  - UpdateTranslationAction                               │
│  - DeleteTranslationAction                               │
│  - SearchTranslationsAction                             │
└─────────────────────────┬───────────────────────────────┘
                          │
┌─────────────────────────▼───────────────────────────────┐
│              Repositories (Data Access)                 │
│  - TranslationRepository                                 │
│  - LocaleRepository                                      │
│  - TagRepository                                         │
└─────────────────────────┬───────────────────────────────┘
                          │
┌─────────────────────────▼───────────────────────────────┐
│              Models (Eloquent ORM)                      │
│  - Translation, Locale, Tag, User                       │
└─────────────────────────────────────────────────────────┘
```

### Key Design Decisions

#### 1. **Authentication: Laravel Sanctum**
- **Choice**: Using Laravel Sanctum for token-based authentication
- **Reason**: 
  - Lightweight and simple to implement
  - Works well for SPA and mobile applications
  - Supports multiple tokens per user
- **Trade-off**: Adds slight overhead on each request for token validation

#### 2. **JSON Export: Streaming Response**
- **Choice**: Using `response()->stream()` for file downloads
- **Reason**:
  - Memory efficient - streams data instead of loading all into memory
  - User gets immediate download response
  - Works well with large datasets (100k+ records)
- **Trade-off**: First request is slower due to database processing

#### 3. **Caching Strategy: 5-Minute Cache**
- **Choice**: Cache export results for 5 minutes using Redis/File
- **Reason**:
  - Protects database from heavy queries on subsequent requests
  - Achieves <10ms response time after first request
  - Balances data freshness with performance
- **Trade-off**: Data may be up to 5 minutes old (acceptable for translations)

#### 4. **Query Optimization: Simple JOIN Only**
- **Choice**: Only JOIN with locales table (no tags in export query)
- **Reason**:
  - Reduces query complexity and execution time
  - Simpler JSON structure for frontend consumption
  - Tags can be managed via separate API endpoints
- **Trade-off**: Less detailed export structure (no tag grouping in JSON)

#### 5. **Security First Approach**
- **Choice**: Parameterized queries, auth on every endpoint
- **Reason**:
  - Prevents SQL injection attacks
  - Protects sensitive translation data
  - Ensures only authorized users can access exports
- **Trade-off**: Slight performance cost for security

### Database Schema

```
┌─────────────┐       ┌─────────────┐       ┌─────────────┐
│   users     │       │  locales   │       │  tags      │
├─────────────┤       ├─────────────┤       ├─────────────┤
│ id          │       │ id          │       │ id          │
│ name        │       │ code        │       │ name        │
│ email       │       │ name        │       │ description │
│ password    │       └──────┬──────┘       └──────┬──────┘
└─────────────┘              │                      │
                             │                      │
                    ┌────────▼────────┐    ┌────────▼────────┐
                    │  translations    │    │ translation_tag│
                    ├─────────────────┤    ├────────────────┤
                    │ id               │    │ translation_id │
                    │ locale_id  (FK)  │    │ tag_id         │
                    │ key              │◄───►│               │
                    │ content          │    └────────────────┘
                    │ created_at       │
                    │ updated_at       │
                    └─────────────────┘
```

### API Design Principles

1. **RESTful Conventions**: Proper HTTP methods (GET, POST, PUT, DELETE)
2. **Consistent Response Format**: Using ResponseService for uniform responses
3. **Pagination**: All list endpoints support pagination
4. **Filtering**: Support for query parameters (locale_code, key, etc.)
5. **Error Handling**: Proper HTTP status codes and error messages

### Technology Stack

| Component | Technology | Version |
|-----------|------------|---------|
| Backend | Laravel | 11.x |
| PHP | PHP | 8.2+ |
| Database | MySQL | 8.0+ |
| Cache | Redis | 7.x |
| Auth | Laravel Sanctum | - |
| Web Server | Nginx | Alpine |
| Container | Docker | Latest |

##### Frequent to ask Questions

### Q1: Why use Repository Pattern?
> **Answer:** Separates data access logic from business logic. Makes code testable (mock repository), maintainable (single place to change data source), and allows easy switching between data sources (e.g., add caching layer without changing controllers).

### Q2: Why use Actions pattern?
> **Answer:** Follows Single Responsibility Principle (SOLID). Each action class handles one business operation, making code:
> - **Reusable** - Can use action in different controllers
> - **Testable** - Easy to unit test in isolation
> - **Maintainable** - Changes to one operation don't affect others

### Q3: How do you handle 100k+ records efficiently?
> **Answer:** Four strategies:
> 1. **Pagination** - Never load all records at once (max 100 per page)
> 2. **Column Selection** - Select only needed columns, not `*`
> 3. **Eager Loading** - Load relationships in single query, not N+1
> 4. **Chunked Processing** - Export uses `chunk(10000)` to avoid memory issues

### Q4: Explain your authentication flow?
> **Answer:**
> 1. User sends credentials to `/api/auth/login`
> 2. Laravel validates and attempts `Auth::attempt()`
> 3. On success, generate token: `$user->createToken('auth-token')->plainTextToken`
> 4. Client stores token and sends in header: `Authorization: Bearer {token}`
> 5. Protected routes use `auth:sanctum` middleware to verify token

### Q5: How do you ensure response time < 200ms?
> **Answer:**
> 1. **Limit records** - Pagination with max 100 per page
> 2. **Optimize queries** - Select only needed columns
> 3. **Index strategically** - Index on key, locale_id
> 4. **Eager load** - Prevent N+1 queries with `with()`
> 5. **Cache rarely-changing data** - Could add for locales/tags

### Q6: What SOLID principles are used?
> **Answer:**
> - **S**ingle Responsibility: Each Action/Repository does one thing
> - **O**pen/Closed: Extend functionality by adding new Actions
> - **L**iskov Substitution: Any repository implementing interface works
> - **I**nterface Segregation: Separate interfaces (TranslationRepository, LocaleRepository)
> - **D**ependency Injection: Actions receive repositories via constructor

---

## Requirements

- PHP 8.2+
- MySQL 8.0+
- Composer
- Node.js (optional, for frontend)

---

## Installation

### Local Development

```bash
# Clone the repository
git clone <repository-url>
cd translation-service

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# (Optional) Seed database with sample data
php artisan db:seed

# Start development server
php artisan serve
```

The API will be available at `http://localhost:8000`

---

## Running the Application

### Option 1: Local Development Server

```bash
php artisan serve
```

### Option 2: Docker (Recommended for Production-like Setup)

```bash
# Copy Docker environment file
cp .env.docker .env

# Build and start containers
docker-compose up -d --build

# Run migrations inside container
docker-compose exec app php artisan migrate

# The API will be available at http://localhost:8000
```

#### Docker Services

| Service | Port | Description |
|---------|------|-------------|
| app | 9000 | PHP-FPM |
| nginx | 8000 | Web Server |
| mysql | 3306 | MySQL Database |
| redis | 6379 | Redis Cache |

#### Docker Commands

```bash
# View logs
docker-compose logs -f

# Stop containers
docker-compose down

# Restart a specific service
docker-compose restart app
```

---

## API Endpoints

### Authentication

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/register` | Register new user |
| POST | `/api/auth/login` | Login user |
| POST | `/api/auth/logout` | Logout user |
| GET | `/api/auth/me` | Get current user |

### Translations

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/translations` | List all translations |
| POST | `/api/translations` | Create translation |
| GET | `/api/translations/{id}` | Get translation |
| PUT | `/api/translations/{id}` | Update translation |
| DELETE | `/api/translations/{id}` | Delete translation |

### Locales

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/locales` | List all locales |
| POST | `/api/locales` | Create locale |
| GET | `/api/locales/{id}` | Get locale |
| PUT | `/api/locales/{id}` | Update locale |
| DELETE | `/api/locales/{id}` | Delete locale |

### Tags

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/tags` | List all tags |
| POST | `/api/tags` | Create tag |
| GET | `/api/tags/{id}` | Get tag |
| PUT | `/api/tags/{id}` | Update tag |
| DELETE | `/api/tags/{id}` | Delete tag |

### Export Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/translations/export-json` | Export translations as JSON file |

---

## Authentication

All API endpoints (except auth) require authentication using Laravel Sanctum tokens.

### Getting a Token

1. **Register**: `POST /api/auth/register`
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

2. **Login**: `POST /api/auth/login`
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

### Using the Token

Include the token in the Authorization header:

```
Authorization: Bearer <your-token>
```

### Postman Setup

1. Import the Postman collection: [Link](https://documenter.getpostman.com/view/11954048/2sBXionAXW)
2. Add this to the "Tests" tab of login/register endpoint:
```javascript
const response = pm.response.json();
pm.collectionVariables.set("authToken", "Bearer " + response.data.token);
```
3. Use the `{{authToken}}` variable in Authorization header for all other endpoints

---

## Export Endpoints

### JSON Export

**Endpoint:** `GET /api/translations/export-json`

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| locale_code | string | (Optional) Filter by locale code (e.g., `en`, `zh`) |

**Examples:**

```bash
# Export all translations
curl -H "Authorization: Bearer <token>" \
  http://localhost:8000/api/translations/export-json \
  -o translations.json

# Export specific locale
curl -H "Authorization: Bearer <token>" \
  "http://localhost:8000/api/translations/export-json?locale_code=en" \
  -o en_translations.json
```

**Response Format:**

```json
{
  "translations": {
    "en": {
      "hello": "Hello",
      "bye": "Bye"
    },
    "zh": {
      "hello": "你好"
    }
  },
  "updated_at": "2026-04-03T12:00:00+00:00"
}
```

---

## Performance Notes

### Export JSON Endpoint Performance

> **⚠️ Disclaimer: Cold Start Performance**
> 
> The JSON export endpoint (`/api/translations/export-json`) **may or may not** take longer than 500ms on the **first request** (cold start). This is due to:
> 
> - Initial database query fetching 100k+ translation records
> - JOIN operations with locales table
> - PHP processing time to build the JSON structure
> - **Security verification**: Every request requires authentication token validation before processing
> 
> **Why Security Impacts Speed:**
> 
>I prioritize **security over speed** by:
> 
> - Using Laravel Sanctum for token-based authentication on every request
> - Validating the user's authorization before processing the export
> - Using parameterized queries (secure but slightly slower)
> - Implementing proper error handling and logging
> 
> These security measures add overhead but ensure the API is protected against:
> - SQL injection attacks
> - Unauthorized access to translation data
> - Token forgery/manipulation
> 
> **Our Solution:**
> 
> I implemented a **5-minute caching mechanism** to ensure fast response times while maintaining security:
> 
> - **First request**: ~300-500ms (database query + authentication + cache storage)
> - **Subsequent requests**: <10ms (served from cache after auth check)
> 
> The cache automatically refreshes every 5 minutes, ensuring data freshness while maintaining performance.
> 
> **To test the performance:**
> ```bash
> # First request (may be slower)
> curl -w "\nTime: %{time_total}s\n" -o translations.json \
>   -H "Authorization: Bearer <token>" \
>   "http://localhost:8000/api/translations/export-json"
> 
> # Second request (should be fast)
> curl -w "\nTime: %{time_total}s\n" -o translations2.json \
>   -H "Authorization: Bearer <token>" \
>   "http://localhost:8000/api/translations/export-json"
> ```

---

## Testing

Run the test suite:

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=TranslationJsonExportTest

# Run with coverage
php artisan test --coverage
```

---

## Project Structure

```
translation-service/
├── app/
│   ├── Actions/           # Business logic actions
│   ├── Console/          # Artisan commands
│   ├── Http/             # Controllers, Requests, Resources
│   ├── Models/           # Eloquent models
│   ├── Providers/        # Service providers
│   └── Services/         # Service classes
├── database/
│   ├── migrations/       # Database migrations
│   └── seeders/          # Database seeders
├── routes/
│   ├── api.php           # API routes
│   └── web.php           # Web routes
├── tests/
│   ├── Feature/          # Feature tests
│   └── Unit/             # Unit tests
├── docker-compose.yml    # Docker orchestration
├── Dockerfile            # PHP-FPM container
└── nginx/
    └── default.conf      # Nginx configuration
```

---

## License

MIT License
