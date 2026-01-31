# Development Process

Development notes and decision log.

## Day 1: Setup & Core Features

### Environment Setup
1. Installed Laravel 11 (as required, though 12.x was available)
2. Configured Laravel Pint with "on save" in PHPStorm
3. Set up Laravel Sail for Docker development

### Module Architecture
1. Used `nwidart/laravel-modules` package for modular structure
2. Created `Modules/Flashcard` with separate routes, controllers, services

### Implementation Decisions
- **Service Layer:** Created `FlashcardService` to handle all business logic, reusable for both CLI and API
- **Unified Requests:** Same validation forms for CLI and API — change once, applies everywhere
- **PHPUnit over Pest:** Chose PHPUnit as it's more common in enterprise Laravel projects

## Day 2: Optional Features & Polish

### Completed
1. Soft deletion, permanent deletion, restore functionality
2. Laravel Auditing for change history (tracks API/CLI access, user, action)
3. History view and version restore based on audit records
4. CI/CD pipeline documentation
5. API documentation with Laravel Scramble
6. Installation guide with Laravel Sail
7. Final code cleanup and container verification

### Testing
- 37 tests total (Unit + Feature)
- Tests helped identify and fix bugs during development
- Full validation coverage not pursued due to time constraints

## Architecture Notes

```
Modules/Flashcard/
├── Console/           # CLI command
├── Http/
│   ├── Controllers/   # API endpoints
│   └── Requests/      # Validation (shared CLI/API)
├── Services/          # Business logic
├── Models/            # Eloquent + Auditing
└── Tests/             # PHPUnit
```
