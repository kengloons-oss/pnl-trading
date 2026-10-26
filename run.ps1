param(
    [int] $Port = 8001,
    [string] $ListenAddress = '127.0.0.1'
)

$ErrorActionPreference = 'Stop'
Set-Location -LiteralPath $PSScriptRoot

if (-not (Test-Path -LiteralPath '.env')) {
    throw 'Missing .env. Configure the database once with connect-supabase.ps1 before starting the app.'
}

if (-not (Test-Path -LiteralPath 'vendor/autoload.php')) {
    throw 'Missing Composer dependencies. Run composer install once before starting the app.'
}

$connectionLine = Get-Content -LiteralPath '.env' | Where-Object { $_ -match '^DB_CONNECTION=' } | Select-Object -Last 1
$connection = if ($connectionLine) { $connectionLine.Substring('DB_CONNECTION='.Length).Trim().Trim('"').Trim("'") } else { 'sqlite' }

if ($connection -eq 'pgsql') {
    $phpPackages = Join-Path $env:LOCALAPPDATA 'Microsoft\WinGet\Packages'
    $phpPackage = Get-ChildItem -LiteralPath $phpPackages -Directory -Filter 'PHP.PHP.8.5*' -ErrorAction SilentlyContinue |
        Sort-Object Name -Descending |
        Where-Object { (Test-Path (Join-Path $_.FullName 'php.exe')) -and (Test-Path (Join-Path $_.FullName 'ext\php_pdo_pgsql.dll')) } |
        Select-Object -First 1

    if (-not $phpPackage) {
        throw 'PHP with the PostgreSQL PDO extension was not found. Install PHP 8.5 from WinGet.'
    }

    $php = Join-Path $phpPackage.FullName 'php.exe'
    $env:Path = "$($phpPackage.FullName);$env:Path"
    & $php -d extension=pdo_pgsql -r "exit(in_array('pgsql', PDO::getAvailableDrivers(), true) ? 0 : 1);"
    if ($LASTEXITCODE -ne 0) {
        throw 'PHP could not load the PostgreSQL PDO driver.'
    }

    & $php -d extension=pdo_pgsql artisan serve --host=$ListenAddress --port=$Port
} else {
    $php = (Get-Command php -ErrorAction Stop).Source
    & $php artisan serve --host=$ListenAddress --port=$Port
}

if ($LASTEXITCODE -ne 0) {
    throw "Laravel server exited with code $LASTEXITCODE."
}
