$Port = 8001
$ErrorActionPreference = 'Stop'
Set-Location -LiteralPath $PSScriptRoot

$phpPackages = Join-Path $env:USERPROFILE 'AppData\Local\Microsoft\WinGet\Packages'
$phpPackage = Get-ChildItem -LiteralPath $phpPackages -Directory -Filter 'PHP.PHP.8.5*' -ErrorAction SilentlyContinue |
    Sort-Object Name -Descending |
    Where-Object { (Test-Path (Join-Path $_.FullName 'php.exe')) -and (Test-Path (Join-Path $_.FullName 'ext\php_pdo_pgsql.dll')) } |
    Select-Object -First 1

if (-not $phpPackage) {
    throw 'PHP with the PostgreSQL PDO extension was not found. Install PHP 8.5 from WinGet, then run this script again.'
}

$php = Join-Path $phpPackage.FullName 'php.exe'
$env:Path = "$($phpPackage.FullName);$env:Path"
$phpExtensionArguments = @()
& $php -r "exit(in_array('pgsql', PDO::getAvailableDrivers(), true) ? 0 : 1);"
if ($LASTEXITCODE -ne 0) {
    & $php -d extension=pdo_pgsql -r "exit(in_array('pgsql', PDO::getAvailableDrivers(), true) ? 0 : 1);"
    if ($LASTEXITCODE -ne 0) {
        throw 'PHP could not load the PostgreSQL PDO driver.'
    }
    $phpExtensionArguments = @('-d', 'extension=pdo_pgsql')
}

if (-not (Test-Path -LiteralPath '.env')) {
    Copy-Item -LiteralPath '.env.example' -Destination '.env'
}

$envPath = Join-Path $PSScriptRoot '.env'
$applicationKeyLine = Get-Content -LiteralPath $envPath | Where-Object { $_ -match '^APP_KEY=' } | Select-Object -Last 1
$applicationKey = if ($applicationKeyLine) { $applicationKeyLine.Substring('APP_KEY='.Length).Trim() } else { '' }

if ([string]::IsNullOrWhiteSpace($applicationKey)) {
    Write-Host 'Generating the Laravel application key...'
    & $php @phpExtensionArguments artisan key:generate
    if ($LASTEXITCODE -ne 0) {
        throw 'Could not generate the Laravel application key.'
    }
}

$passwordPtr = [IntPtr]::Zero
$securePassword = $null
$dbPassword = $null

$savedPasswordLine = Get-Content -LiteralPath $envPath | Where-Object { $_ -match '^DB_PASSWORD=' } | Select-Object -Last 1
if ($savedPasswordLine) {
    $savedPassword = $savedPasswordLine.Substring('DB_PASSWORD='.Length)
    if (-not [string]::IsNullOrWhiteSpace($savedPassword)) {
        $dbPassword = $savedPassword
        Write-Host 'Using the password saved in the local .env file.'
    }
}

if (-not $dbPassword) {
    $securePassword = Read-Host 'Enter the Supabase database password (input is hidden)' -AsSecureString
    $passwordPtr = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($securePassword)
    $dbPassword = [Runtime.InteropServices.Marshal]::PtrToStringBSTR($passwordPtr)
}

try {
    if ([string]::IsNullOrWhiteSpace($dbPassword)) {
        throw 'The password was empty. Run the script again and enter the Supabase database password at the hidden prompt.'
    }

    $envLines = [System.Collections.Generic.List[string]]::new()
    $envLines.AddRange([string[]][System.IO.File]::ReadAllLines($envPath))
    $settings = [ordered]@{
        DB_CONNECTION = 'pgsql'
        DB_URL = ''
        DB_HOST = 'aws-0-ap-northeast-2.pooler.supabase.com'
        DB_PORT = '5432'
        DB_DATABASE = 'postgres'
        DB_USERNAME = 'postgres.augioxxkrwljftvyahnm'
        DB_PASSWORD = $dbPassword
        DB_SSLMODE = 'require'
    }

    foreach ($name in $settings.Keys) {
        $escapedName = [regex]::Escape($name)
        $line = "$name=$($settings[$name])"
        $found = $false
        for ($index = 0; $index -lt $envLines.Count; $index++) {
            if ($envLines[$index] -match "^$escapedName=") {
                $envLines[$index] = $line
                $found = $true
                break
            }
        }
        if (-not $found) {
            $envLines.Add($line)
        }
    }

    [System.IO.File]::WriteAllText($envPath, ($envLines -join "`r`n") + "`r`n", [System.Text.UTF8Encoding]::new($false))
} finally {
    if ($passwordPtr -ne [IntPtr]::Zero) {
        [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($passwordPtr)
    }
    $dbPassword = $null
    if ($securePassword) {
        $securePassword.Dispose()
    }
}

Write-Host 'Checking Supabase connection and applying pending Laravel migrations...'
& $php @phpExtensionArguments artisan migrate
if ($LASTEXITCODE -ne 0) {
    throw "Supabase connection or migration failed (exit code $LASTEXITCODE). Check the connection details and Supabase network restrictions."
}

Write-Host "Supabase is configured. Starting the app at http://127.0.0.1:$Port"
& $php @phpExtensionArguments artisan serve --host=127.0.0.1 --port=$Port
if ($LASTEXITCODE -ne 0) {
    throw "Laravel server exited with code $LASTEXITCODE."
}
