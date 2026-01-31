# Laravel Flashcard CLI

Test assignment. Interactive command-line flashcard application.

## Task Requirements

Build a CLI flashcard app with Laravel 11 using modular architecture:

**Required:**
- Interactive menu (`php artisan flashcard:interactive`)
- Multi-user support with authentication
- CRUD operations for flashcards
- Practice mode with progress tracking (Not Answered / Correct / Incorrect)
- Statistics (completion %, correct answers %)
- Reset progress
- Comprehensive tests (PHPUnit or Pest)

**Optional (bonus):**
- REST API with documentation
- Docker integration (Laravel Sail)
- CI/CD pipeline description
- Comprehensive logging
- Change history with version restore
- Soft delete with restore

## What I Built

All required features + all optional features implemented.

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan module:migrate --seed Flashcard
php artisan flashcard:interactive
```

### Features Completed

- **Interactive CLI Menu** - Full practice flow from terminal
- **Multi-user Support** - Each user sees only their own cards (Laravel Sanctum)
- **Practice Mode** - Track progress per question
- **Statistics** - Completion percentage, correct answers ratio
- **REST API** - Full CRUD + authentication (see `docs/API.md`)
- **Soft Delete** - Restore deleted cards
- **Audit History** - View and revert to previous versions (Laravel Auditing)
- **Tests** - 37 tests: Unit + Feature (PHPUnit)
- **Docker** - Laravel Sail setup
- **CI/CD** - Pipeline description (see `docs/CI_CD.md`)

### Tech Stack

- Laravel 11
- Modular architecture (`Modules/Flashcard` via nwidart/laravel-modules)
- Laravel Sanctum (auth)
- Laravel Auditing (history)
- SQLite/MySQL
- Docker (Laravel Sail)
- PHPUnit

### Documentation

- `docs/API.md` - API endpoints and authentication
- `docs/CI_CD.md` - CI/CD pipeline description
- `docs/README.md` - Installation guide
