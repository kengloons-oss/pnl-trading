# PnL Trading — Setup Guide

This guide gets the web app running on a new Windows 10/11 computer from GitHub. The app uses Laravel and the existing Supabase PostgreSQL database. First setup asks for the Supabase database password once, applies pending migrations, then starts the server.

## 1. Install prerequisites

Install Git, PHP 8.5, and Composer. The PowerShell scripts use the WinGet PHP 8.5 package and load its PostgreSQL PDO driver.

Run in PowerShell:

```powershell
winget install --id Git.Git --exact
winget install --id PHP.PHP.8.5 --exact
winget install --id Composer.Composer --exact
```

Close and reopen PowerShell after installing. Check that the tools are available:

```powershell
git --version
php -v
composer --version
```

If `php -v` shows a version other than PHP 8.5, make sure the WinGet PHP 8.5 directory is earlier in `PATH` than other PHP installations. The app runner locates the WinGet PHP package directly, while Composer uses the PHP executable on `PATH`.

## 2. Clone the GitHub repository

Choose a folder for the project, then run:

```powershell
cd $HOME\Desktop
git clone https://github.com/kengloons-oss/pnl-trading.git
cd .\pnl-trading
```

## 3. Install PHP dependencies

From the project folder:

```powershell
composer install
```

This installs the exact Laravel dependencies listed in `composer.lock`. Node.js and `npm install` are not needed for the current pages and styles.

## 4. First-time database setup and start

Run the setup script with a one-time execution-policy bypass:

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File .\connect-supabase.ps1
```

The script will:

1. Create `.env` from `.env.example` if it does not exist.
2. Generate a Laravel `APP_KEY` if one is missing.
3. Ask for the Supabase database password in a hidden prompt if it is not already in the local `.env` file.
4. Save the connection settings and password to `.env` on this computer. `.env` is ignored by Git.
5. Check the PostgreSQL driver, run pending Laravel migrations, and start the local server.

Use the Supabase database password supplied by the project owner. Do not put it into a command, source file, or GitHub. The script is configured for this project's existing Supabase pooler; change its connection settings only when intentionally connecting to a different Supabase project.

When the server reports it is listening, open:

```text
http://127.0.0.1:8001
```

Keep that PowerShell window open while using the app. Press **Ctrl+C** in that window to stop the server.

## 5. Normal startup after setup

After `.env` and `vendor` exist, start the app with:

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File .\run.ps1
```

This starts Laravel on `http://127.0.0.1:8001` without asking for the database password and without running migrations. If port 8001 is occupied, choose another port:

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File .\run.ps1 -Port 8002
```

## 6. After downloading code updates

Pull the latest code:

```powershell
git pull
composer install
```

If the update includes a database migration, run the setup script again. It reuses the password already saved in `.env`, applies pending migrations, then starts the server:

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File .\connect-supabase.ps1
```

If there are no database migrations, start normally with `run.ps1`.

## 7. First use

1. Open `http://127.0.0.1:8001` and register a user.
2. Add a trading account under **Accounts**.
3. Record a trade manually under **Trades**, or use **Import** to upload a CSV, TSV, or tab-separated TXT file.
4. Use [sample-trades.csv](sample-trades.csv) to try the importer. It contains three distinct records and one repeated Trade ID; the repeated row should be skipped.
5. Open **Markets** to view the BTCUSD, ETHUSD, USDJPY, and XAUUSD charts. The charts load from TradingView and require an internet connection.

## Troubleshooting

### PowerShell says script execution is disabled

Use the commands above with `-ExecutionPolicy Bypass`. This bypass applies only to that PowerShell process; it does not permanently change the computer's execution policy.

### `could not find driver` or PostgreSQL connection errors

Start using `run.ps1` or `connect-supabase.ps1`, not a PHP executable without the PostgreSQL PDO driver. The scripts locate the WinGet PHP 8.5 package and load `pdo_pgsql` for Laravel commands. If the script cannot find the driver, install PHP 8.5 with WinGet and reopen PowerShell.

### Database password prompt appears again

The password is saved in the local `.env`. If `.env` was deleted or recreated, the script will ask for it again. Enter it at the hidden prompt; do not include it in a command or share it on GitHub.

### Connection or authentication fails

Confirm that the password is the Supabase **database password**, not a Supabase publishable or secret API key. Check that the Supabase project is available and that its database password has not changed. If it has changed, update `DB_PASSWORD` in the local `.env` or clear that value and rerun `connect-supabase.ps1` to enter the new password.

### Browser says the site cannot be reached

Confirm the PowerShell window still shows the server running. Open the port shown in that window; the default is `http://127.0.0.1:8001`.

## Local secrets and data

- `.env` holds the local app key and Supabase credentials. It is excluded from Git.
- Never commit `.env`, database passwords, or API secrets.
- The app uses the shared configured Supabase database. Trades saved there are real database records visible to authorized users; use sample data only when appropriate.
- Uploaded import files are stored privately during the import flow and removed after a successful import.

## Optional local SQLite development

The scripts in this repository are tailored to Windows and the configured Supabase PostgreSQL database. For a separate SQLite environment, copy `.env.example` to `.env`, generate an app key with `php artisan key:generate`, configure SQLite in `.env`, create `database/database.sqlite`, then run `php artisan migrate`. Keep this environment separate from the shared Supabase database.
