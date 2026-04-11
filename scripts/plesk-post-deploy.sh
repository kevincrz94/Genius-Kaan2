#!/usr/bin/env bash
set -euo pipefail

composer install --no-dev --optimize-autoloader

php artisan migrate --force
php artisan storage:link || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

if command -v npm >/dev/null 2>&1; then
  npm ci
  npm run build
else
  echo "npm no esta disponible; sube public/build ya compilado si usas Vite."
fi

php artisan about
