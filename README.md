# PnL Trading

A multi-user, multi-market trading journal built with PHP and Laravel. It includes trade and account management, CSV/TSV import with duplicate skipping, monthly and yearly dashboard views, and embedded market charts for BTCUSD, ETHUSD, USDJPY, and XAUUSD.

## Get started

For a new Windows computer, follow [Setup.md](Setup.md). It covers cloning this repository, installing dependencies, connecting to the configured Supabase database, and starting the app.

After the one-time setup, run `run.ps1` and open `http://127.0.0.1:8001`. The startup script does not ask for the database password or run migrations. To apply new database migrations after updating the code, run `connect-supabase.ps1`; it reuses the password stored in the local ignored `.env` file and starts the app after migrations complete.

## Current features

- User registration and sign-in with per-user account and trade ownership.
- Multiple trading accounts and complete-position trade CRUD.
- CSV, TSV, and tab-separated text import with column mapping, preview, row validation, and duplicate skipping.
- Monthly and yearly performance dashboard, grouped by original currency and user's reporting timezone.
- TradingView embedded charts for BTCUSD, ETHUSD, USDJPY, and XAUUSD.

Excel workbooks, OCR, AI analysis, richer partial-close workflows, and MT5 synchronization remain future work. See [plan.md](plan.md) for the product plan.

## Security

`.env` contains local credentials and is excluded from Git. Never commit database passwords, API keys, or production secrets. The Supabase connection script stores the password only in the local `.env` file after a hidden prompt.
