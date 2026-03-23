# CLAUDE.md

## Project Overview

Poppy Seed Pets is a browser-based pet adoption and activity simulation game (poppyseedpets.com). Monorepo with two independent apps:

- **`api/`** — Symfony 7.3 (PHP 8.4) backend with MySQL + Redis
- **`webapp/`** — Angular 20 frontend (TypeScript, SCSS, Angular Material)

## Non-Standard Commands

- **API lint**: `composer run php-cs-fixer-dry-run` / `composer run php-cs-fixer` (in `api/`)
- **API static analysis**: `vendor/bin/phpstan --configuration=phpstan.dist.neon` (in `api/`)
- **Cron (manual)**: `vendor/bin/crunz schedule:run` (in `api/`)
- **Storybook**: `ng run PoppySeedPetsApp:storybook` (in `webapp/`)
- **Quick start**: `install.bat` / `run.bat` from repo root

## Architecture Decisions

These are intentional design choices — follow them in new code:

### One Endpoint Per Controller
Each controller class has exactly one endpoint. Request and response DTOs live in the same file as the controller. Don't share DTOs between endpoints except for truly common data (user/pet data).

### No Doctrine Repositories
Query the entity manager directly in services or static helper classes. Don't create repository classes — they become dumping grounds for unrelated queries.

### No Serialization Groups
Use explicit mapping to response DTOs instead. Serialization groups scatter related code across entity files. (Legacy code still uses them — migrate when touching it.)

### Use `#[MapRequestPayload]` for Request DTOs
Modern Symfony request handling. Migrate old code to use this when touching it.

### Vertical Slices Over Technical Layers
Organize by game feature (Fishing, Cooking, Beehive), not by technical concern (controllers, services, repositories). Think slightly CQRS-ish.

### Logic in Controllers is Fine
Start with logic in the controller. Extract to a service only when it needs to be shared between endpoints. YAGNI.

### POST URLs Read Like Actions
`POST /florist/tradeForGiftPackage`, `POST /pet/{petId}/feed` — RPC-style over CRUD-style. GET requests must never modify data (except side effects like logging).

## Key Architectural Patterns

### ResponseService (Critical)
Every API endpoint must return via `ResponseService`. It:
- Injects current user data into every response
- Delivers unread pet activity logs as "flash messages"
- Sets reload flags (`reloadInventory`, `reloadPets`) for the frontend
- Normalizes response structure: `{ success, data, activity, user, reloadInventory, reloadPets }`

### Pet Activity System
Core game loop documented in detail at `api/src/Service/PetActivity/CLAUDE.md`. Key flow:
1. Cron increments `activity_time` every minute (max 2880 min / 48 hours)
2. Player visits house → pets with 60+ minutes consume time and perform activities
3. Activities implement `IPetActivity` interface with `groupDesire()` (weighted random selection) and `possibilities()`
4. Results tracked via `PetActivityLog` → delivered as flash messages through `ResponseService`

### Lazy-Loaded Services
`PetActivity/` and `Holidays/` service trees are configured as lazy in `config/services.yaml`. Don't put expensive logic in constructors of these services.

### Testability Abstractions
- Use `Clock` service instead of `new \DateTime()` / `new \DateTimeImmutable()`
- Use `IRandom` service instead of `rand()` / `random_int()`
- Both are mockable for deterministic tests

### Service Layer Details
See `api/src/Service/CLAUDE.md` for ResponseService patterns, activity log creation, and service conventions.

## Cron Jobs (api/tasks/AllTasks.php)

- **Every minute**: `app:increase-time` (pet activity time), `app:run-park-events`
- **Hourly**: `app:buzz-buzz` (beehive production)
- **Daily**: `app:calculate-daily-market-item-averages`, `app:calculate-daily-stats`

## Frontend Notes

- Dev server requires HTTPS — uses `dev.key`/`dev.pem` from repo root (expire Dec 2033)
- Proprietary assets expected at `../../PoppySeedPetsAppProprietaryAssets/` relative to `webapp/` — app builds without them but images will be missing
- API URL configured in `webapp/src/environments/environment.ts` (defaults to `https://localhost:8000`)

## Database

- No seed data in repo — game data (recipes, items, NPCs) must be sourced separately
