# Repository Guidelines

## Project Structure & Module Organization
- Source: `app/` (domain logic, `Http/`, `Models/`, `Livewire/`).
- Routes: HTTP in `routes/web.php`, Artisan in `routes/console.php`.
- Views & assets: `resources/views`, `resources/js`, `resources/css` (built with Vite).
- Config & env: `config/`, `.env` (do not commit secrets).
- Database: `database/migrations`, `database/factories`, `database/seeders`.
- Public entrypoint: `public/`. Tests: `tests/Feature`, `tests/Unit`.

## Build, Test, and Development Commands
- Install deps: `composer install` and `npm install`.
- Local dev (app + queue + logs + Vite): `composer run dev`.
- PHP server only: `php artisan serve`.
- Frontend dev: `npm run dev`; production build: `npm run build`.
- Tests: `composer test` or `php artisan test`.
- First run: `cp .env.example .env` then `php artisan key:generate`.
- Database: `php artisan migrate` (add `--seed` if needed).

## Coding Style & Naming Conventions
- PHP: PSR-12, 4-space indentation, UTF-8, no trailing whitespace.
- Naming: Classes StudlyCase; methods/variables camelCase; DB tables snake_case plural.
- Namespaces: PSR-4 under `App\` (e.g., `App\Http\Controllers\UserController`).
- Livewire: components in `app/Livewire`, views in `resources/views/livewire`.
- Formatting: run `./vendor/bin/pint` locally (use `--test` in CI).

## Testing Guidelines
- Framework: PHPUnit via `phpunit.xml` and `php artisan test`.
- Location: feature tests in `tests/Feature`, unit tests in `tests/Unit`.
- Naming: end files with `Test.php` (e.g., `UserProfileTest.php`).
- Isolation: prefer in-memory/sqlite or `RefreshDatabase` for DB tests.

## Commit & Pull Request Guidelines
- Commits: imperative, small, and scoped. Examples: `fix: correct login redirect`, `feat: add profile photo upload`.
- PRs: include purpose, summary of changes, screenshots for UI, migration notes, and linked issues (e.g., `Closes #123`).
- Verification: run `composer test`, `./vendor/bin/pint`, and ensure `npm run build` passes before opening/merging.

## Security & Configuration Tips
- Copy env: `cp .env.example .env`, then set `APP_KEY`, DB credentials, and mail settings.
- Never commit `.env`, secrets, or generated keys.
- Local queues/logs: `composer run dev` also runs `queue:listen` and `pail` for streamlined logs.

