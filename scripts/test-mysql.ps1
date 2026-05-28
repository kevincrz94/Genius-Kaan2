param(
    [int]$Port = 3307,
    [string]$Database = 'impulse_test'
)

$ErrorActionPreference = 'Stop'

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$projectRoot = Split-Path -Parent $scriptDir
$starter = Join-Path $scriptDir 'start-mysql-test.ps1'

& $starter -Port $Port -Database $Database

Push-Location $projectRoot

try {
    $env:DB_CONNECTION = 'mysql'
    $env:DB_HOST = '127.0.0.1'
    $env:DB_PORT = "$Port"
    $env:DB_DATABASE = $Database
    $env:DB_USERNAME = 'root'
    $env:DB_PASSWORD = ''

    php artisan test
    exit $LASTEXITCODE
} finally {
    Pop-Location
}
