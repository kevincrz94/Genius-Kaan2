# Deploy en Hostinger

Este proyecto es una aplicacion Laravel 12 con frontend Vite/React.

## Preset correcto

Si Hostinger pregunta por **Framework preset**, selecciona:

- `Laravel`, si aparece en la lista.
- `PHP` o `Other PHP`, si no aparece Laravel.

No selecciones `PrestaShop`, `WordPress`, `Node`, `React`, `Vue` ni `Static site`. React/Vite aqui solo compila assets para Laravel; la aplicacion que sirve el sitio es PHP/Laravel.

## Requisitos

- PHP 8.2 o superior.
- Composer.
- Node.js solo para compilar assets, no para ejecutar el sitio en produccion.
- Base de datos MySQL o MariaDB.
- El dominio debe apuntar al directorio `public`.

## Variables de entorno

En Hostinger crea un archivo `.env` en la raiz del proyecto usando `.env.hostinger.example` como base.

Valores minimos que debes ajustar:

```env
APP_NAME="Genius Kaan"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com

ADMIN_EMAIL=admin@tu-dominio.com
ADMIN_PASSWORD=define-una-clave-segura

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nombre_base_hostinger
DB_USERNAME=usuario_base_hostinger
DB_PASSWORD=clave_base_hostinger

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

COGNIFIT_CLIENT_ID=
COGNIFIT_CLIENT_SECRET=
COGNIFIT_HASH=
COGNIFIT_LAUNCH_URL=
COGNIFIT_API_BASE_URL=https://api.cognifit.com
```

## Comandos de deploy

Ejecuta en la raiz del proyecto:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan key:generate --force
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Si ya existe `APP_KEY` en `.env`, no vuelvas a ejecutar `php artisan key:generate`, porque invalidaria datos cifrados existentes.

## Public root

Laravel debe servir desde:

```text
public/
```

Si Hostinger permite configurar el directorio raiz del dominio, ponlo apuntando a `public`.

Si estas en hosting compartido y no permite cambiar el document root, sube el proyecto fuera de `public_html` y copia el contenido de `public/` dentro de `public_html`, ajustando en `public_html/index.php` las rutas a:

```php
require __DIR__.'/../nombre-carpeta-proyecto/vendor/autoload.php';
$app = require_once __DIR__.'/../nombre-carpeta-proyecto/bootstrap/app.php';
```

## Tareas programadas

Si la app usa scheduler de Laravel, agrega un cron job:

```bash
* * * * * cd /home/USUARIO/ruta-del-proyecto && php artisan schedule:run >> /dev/null 2>&1
```

Si usa colas con `QUEUE_CONNECTION=database`, configura un proceso persistente o cron para:

```bash
php artisan queue:work --tries=3
```

En hosting compartido, si no hay proceso persistente, usa un cron temporal cada minuto con `queue:work --stop-when-empty`.

## Despues de cada actualizacion

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
