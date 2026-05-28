param(
    [int]$Port = 3307,
    [string]$Database = 'genius_kaan_review',
    [string]$DumpPath = ''
)

$ErrorActionPreference = 'Stop'

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$projectRoot = Split-Path -Parent $scriptDir
$starter = Join-Path $scriptDir 'start-mysql-test.ps1'
$mysql = 'C:\xampp\mysql\bin\mysql.exe'

if ($DumpPath -eq '') {
    $DumpPath = Join-Path $projectRoot 'ejemplo\u488629835_genius_kaan.sql'
}

if (-not (Test-Path $DumpPath)) {
    throw "No se encontro el dump SQL en $DumpPath"
}

& $starter -Port $Port

& $mysql --host=127.0.0.1 --protocol=tcp --port=$Port --user=root --password= -e "DROP DATABASE IF EXISTS $Database; CREATE DATABASE $Database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

if ($LASTEXITCODE -ne 0) {
    throw "No se pudo preparar la base $Database."
}

Get-Content -Raw $DumpPath | & $mysql --host=127.0.0.1 --protocol=tcp --port=$Port --user=root --password= $Database

if ($LASTEXITCODE -ne 0) {
    throw "No se pudo importar el dump en $Database."
}

& $mysql --host=127.0.0.1 --protocol=tcp --port=$Port --user=root --password= -D $Database -e "SHOW TABLES;"

if ($LASTEXITCODE -ne 0) {
    throw "La importacion termino pero no se pudo validar la base $Database."
}

Write-Output "Dump importado en $Database desde $DumpPath"
