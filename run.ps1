param(
    [int] $Port = 8000,
    [string] $ListenAddress = '127.0.0.1'
)

$ErrorActionPreference = 'Stop'
Set-Location -LiteralPath $PSScriptRoot

function Invoke-CheckedCommand {
    param(
        [Parameter(Mandatory = $true)][string] $Executable,
        [Parameter(Mandatory = $true)][string[]] $Arguments
    )

    & $Executable @Arguments
    if ($LASTEXITCODE -ne 0) {
        throw "Command failed with exit code ${LASTEXITCODE}: $Executable $($Arguments -join ' ')"
    }
}

$php = (Get-Command php -ErrorAction Stop).Source
$composer = (Get-Command composer -ErrorAction Stop).Source

Write-Host 'Installing PHP dependencies...'
Invoke-CheckedCommand -Executable $composer -Arguments @('install', '--no-interaction')

if (-not (Test-Path -LiteralPath '.env')) {
    Copy-Item -LiteralPath '.env.example' -Destination '.env'
    Write-Host 'Created .env from .env.example.'
}

$appKeyLine = Get-Content -LiteralPath '.env' | Where-Object { $_ -match '^APP_KEY=' } | Select-Object -First 1
if (-not $appKeyLine -or $appKeyLine -match '^APP_KEY=\s*$') {
    Invoke-CheckedCommand -Executable $php -Arguments @('artisan', 'key:generate')
}

if (-not (Test-Path -LiteralPath 'database/database.sqlite')) {
    New-Item -ItemType File -Path 'database/database.sqlite' | Out-Null
    Write-Host 'Created the SQLite database file.'
}

Write-Host 'Applying pending database migrations...'
Invoke-CheckedCommand -Executable $php -Arguments @('artisan', 'migrate')

Write-Host "Starting PnL Trading at http://${ListenAddress}:$Port"
Invoke-CheckedCommand -Executable $php -Arguments @('artisan', 'serve', '--host', $ListenAddress, '--port', "$Port")
