# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Poppy Seed Pets API is a Symfony-based REST API backend for a virtual pet game. The codebase is written in PHP 8.4 and uses Doctrine ORM, MySQL/MariaDB, and Redis for caching.

## Essential Development Commands

### Setup
```bash
composer install                                    # Install dependencies
php bin/console doctrine:migrations:migrate         # Run database migrations
```

### Development Server
```bash
symfony server:start                                # Start local development server
```

### Testing
```bash
vendor/bin/phpunit                                  # Run all tests
vendor/bin/phpunit --exclude-group requiresDatabase # Run tests without database
vendor/bin/phpunit tests/Specific/TestFile.php      # Run a single test file
```

### Code Quality
```bash
php vendor/bin/phpstan analyse                      # Run static analysis (level 10)
composer audit                                      # Check for security vulnerabilities
composer php-cs-fixer-dry-run                       # Check PSR-4 autoloading compliance
```

Note: `php-cs-fixer` is configured minimally to only check PSR-4 autoloading (class namespaces match file paths). It does not enforce code style rules. PSR-4 violations should be fixed manually.

### Cron Tasks
Cron tasks are managed by Crunz and defined in `tasks/AllTasks.php`. To run cron tasks locally, add to crontab:
```
* * * * * cd /PATH_TO_PROJECT && vendor/bin/crunz schedule:run
```

## Architectural Principles

### One Endpoint Per Controller (Strict)
Every controller class must contain exactly one endpoint. The same file should also contain the endpoint's request and response DTOs.

**Example structure:**
```php
// src/Controller/House/DoQualityTimeController.php
class DoQualityTimeController {
    #[Route("/house/doQualityTime", methods: ["POST"])]
    public function doQualityTime(...): JsonResponse { }
}
// Request/Response DTOs can be in the same file
```

Controllers are organized in nested directories under `src/Controller/` that mirror the API structure (e.g., `House/`, `Beehive/`, `Patreon/`).

### No Doctrine Repository Classes
**Do not use Doctrine repository classes.** This is enforced by CI pipeline checks that fail if `ServiceEntityRepository` or `repositoryClass:` are found.

Instead:
- Write queries directly in controller endpoints using `EntityManagerInterface`
- Create domain-specific service classes when queries need to be shared
- Use static helper classes for shared query logic

**Why:** Repository classes become bloated with unrelated code; most queries are single-use.

### No Serialization Groups (Legacy Pattern)
**Avoid Symfony serialization groups.** Use explicit mapping to response DTOs instead.

**Legacy note:** The codebase contains serialization groups (e.g., `SerializationGroupEnum::PET_ACTIVITY_LOGS`) because it started with this pattern. When touching old code, migrate to explicit DTO mapping.

### RESTish URLs (Action-Oriented)
Endpoints should read like actions to be taken, not strict REST resources:
- `POST /florist/tradeForGiftPackage`
- `POST /fireplace/feedWhelp`
- `POST /pet/{petId}/feed`
- `PATCH /letter/{letterId}/read`

GET requests must not modify data (except logging/tracking).

### Controllers May Contain Logic
1. **Start** by putting logic directly in controller endpoints
2. **Extract** logic to service classes only when it needs to be shared between endpoints

This prevents premature abstraction.

### Request/Response DTOs Should Not Be Shared
Request and response DTOs should be endpoint-specific, defined in the same file as the controller. Exception: truly common data like pet/player data.

**Migration note:** Symfony now supports `#[MapRequestPayload]` and `#[MapQueryString]` for request DTOs. Migrate old code to use these when possible.

## Code Architecture

### Key Directories
- **`src/Controller/`** - API endpoints organized by feature (House, Beehive, Plaza, etc.)
- **`src/Service/`** - Shared business logic services
  - `src/Service/PetActivity/` - Pet activity systems (lazy-loaded)
  - `src/Service/Holidays/` - Holiday-specific logic (lazy-loaded)
- **`src/Entity/`** - Doctrine entities (User, Pet, Item, Inventory, etc.)
- **`src/Enum/`** - Typed enumerations
- **`src/Functions/`** - Static utility functions (e.g., `ItemRepository`, `PetSpeciesRepository`)
- **`src/Command/`** - Console commands (many for stats/cron jobs)
- **`tasks/`** - Crunz cron task definitions
- **`migrations/`** - Doctrine database migrations
- **`tests/`** - PHPUnit tests

### ResponseService Pattern
All controller endpoints should return responses via `ResponseService`:

```php
return $responseService->success([
    'message' => $message,
    'data' => $data
]);
```

`ResponseService` handles:
- Injecting user data into all responses
- Including unread pet activity logs ("flash messages")
- Setting reload flags (`reloadInventory`, `reloadPets`)
- Normalizing data with serialization groups

### License Headers
All PHP files must include the GPL 3.0 license header (see example in any existing `.php` file). This is enforced by the `license-eye` tool in CI.

### Lazy-Loaded Services
Pet activity services and holiday services are configured as lazy-loaded in `config/services.yaml` to optimize performance.

## CI/CD Pipeline

The GitHub Actions workflow (`.github/workflows/php.yml`) runs on all PRs and includes:

1. **Repository pattern check** - Fails if Doctrine repositories are used
2. **License header check** - Validates GPL headers on all files
3. **Syntax check** - Validates PHP syntax on changed files
4. **Composer audit** - Checks for known vulnerabilities
5. **PSR-4 check** - `composer php-cs-fixer-dry-run`
6. **PHPStan** - Static analysis at level 10 with baseline
7. **PHPUnit** - Tests excluding `requiresDatabase` group

## Environment Configuration

- `.env` - Default configuration (committed)
- `.env.local` - Local overrides (not committed)
- `.env.test` - Test environment config

Key environment variables:
- `APP_ENV` - Application environment (dev/prod/test)
- `DATABASE_URL` - MySQL/MariaDB connection
- `REDIS_URL` - Redis cache connection
- Various API credentials for Patreon, Reddit, AWS SES, etc.

## Testing Notes

Tests use PHPUnit 9.6. Test groups:
- `requiresDatabase` - Tests that need a database connection (excluded from CI)

Bootstrap file: `tests/bootstrap.php`

## Common Gotchas

- The codebase has a `phpstan-baseline.neon` with existing violations - new code should not add to it
- All new code must follow PSR-4 autoloading standards (enforced by php-cs-fixer)
- The project uses strict types: `declare(strict_types=1);` in all files
- Controllers use Symfony attributes for routing: `#[Route(...)]`
- Use `UserAccessor` service to get the current authenticated user
- The `DoesNotRequireHouseHours` attribute is used on endpoints that don't consume house hours
