# PnL Trading

A private, multi-market trading journal built with PHP and Laravel. This first slice includes user accounts, trading accounts, complete-position CRUD, and a reporting dashboard. Supabase and Render settings are documented as environment variables and are not required for local development.

## Local setup

1. Install PHP 8.3+, Composer, and Node.js if you later add bundled frontend assets.
2. Install PHP dependencies with `composer install`.
3. Copy `.env.example` to `.env` and run `php artisan key:generate`.
4. Create an empty SQLite file at `database/database.sqlite` if it does not exist.
5. Run `php artisan migrate`.
6. Start the app with `php artisan serve` and open `http://127.0.0.1:8000`.

Create an account in the browser. Add a trading account, then record a position. Reporting uses the manual PnL value when provided, applies the selected fee treatment, and groups closed positions in the user's reporting timezone.

## Database

SQLite is configured for local development. To use Supabase PostgreSQL, set `DB_CONNECTION=pgsql` and fill in the PostgreSQL connection values shown in `.env.example`. Do not commit `.env` or production credentials.

## Current scope

- Multi-user registration and sign-in.
- Multiple trading accounts per user.
- Manual trade positions across forex, crypto, equities, and other markets.
- Original currency codes and decimal precision fields.
- Dashboard filters for monthly and yearly reporting.
- Execution child records are modeled for future partial-entry and partial-exit capture.

CSV/Excel import, live market feeds, position-level execution capture, AI analysis, OCR, and MT5 sync are planned follow-up work.
