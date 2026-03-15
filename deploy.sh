#!/bin/bash
# Script de déploiement Healthcare Balance
set -e

echo "=== Healthcare Balance — déploiement ==="

composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache 2>/dev/null || true

echo "Cache optimisé. Pensez à lancer : php artisan migrate --force"
echo "Et à démarrer le queue worker : php artisan queue:work --tries=3"
