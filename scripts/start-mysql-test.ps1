param(
    [int]$Port = 3307,
    [string]$MysqlBin = 'C:\xampp\mysql\bin',
    [string]$BaseDir = 'C:\tmp\gk-mysql-test',
    [string]$Database = 'impulse_test'
)

$ErrorActionPreference = 'Stop'

function Test-MySqlPort {
    param([int]$LocalPort)

    return [bool] (netstat -ano | Select-String -Pattern ":$LocalPort\s+.*LISTENING")
}

$mysqld = Join-Path $MysqlBin 'mysqld.exe'
$mysql = Join-Path $MysqlBin 'mysql.exe'
$mysqlInstallDb = Join-Path $MysqlBin 'mysql_install_db.exe'
$dataDir = Join-Path $BaseDir 'data'
$tmpDir = Join-Path $BaseDir 'tmp'
$defaultsFile = Join-Path $dataDir 'my.ini'

if (-not (Test-Path $mysqld)) {
    throw "No se encontro mysqld.exe en $MysqlBin"
}

if (-not (Test-Path $mysql)) {
    throw "No se encontro mysql.exe en $MysqlBin"
}

if (-not (Test-Path $mysqlInstallDb)) {
    throw "No se encontro mysql_install_db.exe en $MysqlBin"
}

New-Item -ItemType Directory -Force $dataDir | Out-Null
New-Item -ItemType Directory -Force $tmpDir | Out-Null

if (-not (Test-Path $defaultsFile)) {
    & $mysqlInstallDb -d $dataDir -P $Port -s

    if ($LASTEXITCODE -ne 0) {
        throw "No se pudo inicializar la instancia temporal de MySQL."
    }
}

if (-not (Test-MySqlPort -LocalPort $Port)) {
    $null = Start-Process -FilePath $mysqld -ArgumentList "--defaults-file=$defaultsFile", '--console' -WindowStyle Hidden -PassThru

    $ready = $false

    foreach ($attempt in 1..20) {
        Start-Sleep -Milliseconds 500

        if (Test-MySqlPort -LocalPort $Port) {
            $ready = $true
            break
        }
    }

    if (-not $ready) {
        throw "La instancia temporal de MySQL no quedo escuchando en el puerto $Port."
    }
}

& $mysql --host=127.0.0.1 --protocol=tcp --port=$Port --user=root --password= -e "CREATE DATABASE IF NOT EXISTS $Database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

if ($LASTEXITCODE -ne 0) {
    throw "No se pudo crear o validar la base $Database en el puerto $Port."
}

Write-Output "MySQL temporal listo en 127.0.0.1:$Port con base $Database"
