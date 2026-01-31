# Installation Guide

## Quick Start

```bash
git clone <repo>
cd <repo>
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan module:migrate --seed Flashcard
```

## Running with Docker (Laravel Sail)

```bash
./vendor/bin/sail up
./vendor/bin/sail artisan flashcard:interactive
```

## Running without Docker

```bash
php artisan serve
php artisan flashcard:interactive
```

## Test Users

Seeder creates 3 test users:
- `user_0` / `user_1` / `user_2`
- Password: `password`

Each user has sample flashcards pre-loaded.

## Testing

```bash
php artisan test
```

## API

See `API.md` for endpoints and authentication.
