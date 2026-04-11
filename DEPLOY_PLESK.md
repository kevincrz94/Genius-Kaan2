# Despliegue en Plesk/Ionos

## Requisitos

- PHP 8.2 o superior.
- Extensiones PHP: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `gd`, `json`, `mbstring`, `openssl`, `pdo_pgsql`, `pgsql`, `sodium`, `tokenizer`, `xml`, `zip`.
- Composer 2.
- PostgreSQL creado desde Plesk.
- Node/NPM solo si vas a compilar los assets React/Vite en el servidor.

## Configuracion de Plesk

1. Crea la base PostgreSQL y su usuario desde Plesk.
2. Configura el document root del dominio hacia la carpeta `public`.
3. Copia `.env.plesk.example` como `.env`.
4. Llena `APP_URL`, datos `DB_*` y variables `COGNIFIT_*`.
5. No pegues credenciales de Cognifit en el repositorio.

## Comandos de despliegue

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate --force
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Si el servidor tiene Node/NPM:

```bash
npm ci
npm run build
```

Si no tiene Node/NPM, compila `public/build` antes de subir el proyecto.

## Validacion

Comprueba estos endpoints:

```text
https://TU-DOMINIO.COM/api/health
https://TU-DOMINIO.COM/api/cognifit/status
```

`/api/health` debe responder `status: ok` cuando PostgreSQL este accesible.
`/api/cognifit/status` debe responder `configured: true` cuando las credenciales de Cognifit esten completas.

## Flutter

Ejecuta la app apuntando al dominio real:

```powershell
flutter run --dart-define=API_BASE_URL=https://TU-DOMINIO.COM/api
```
