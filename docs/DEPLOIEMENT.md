# Déploiement — Healthcare Balance

## Prérequis

- PHP 8.2+
- Composer
- Node.js 18+
- MariaDB 10.6+ ou MySQL 8+
- Queue worker (pour jobs et rappels)

## Étapes

### 1. Serveur (VPS / Forge)

```bash
# Cloner le projet
git clone <repo> /var/www/healthcare-balance
cd /var/www/healthcare-balance

# Installer les dépendances
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# Configuration
cp .env.example .env
php artisan key:generate
# Éditer .env : DB_*, APP_URL, MAIL_*, TWILIO_* (optionnel)
```

### 2. Base de données

```bash
php artisan migrate --force
php artisan db:seed  # RoleSeeder, PoleSeeder
```

### 3. Permissions

```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### 4. Queue (rappels email/SMS)

Configurer un worker (Supervisor, Forge, systemd) :

```ini
[program:healthcare-balance-queue]
command=php /var/www/healthcare-balance/artisan queue:work --sleep=3 --tries=3
directory=/var/www/healthcare-balance
user=www-data
numprocs=1
autostart=true
autorestart=true
```

### 5. Planification (rappels)

Cron :

```bash
* * * * * cd /var/www/healthcare-balance && php artisan schedule:run >> /dev/null 2>&1
```

### 6. Twilio (SMS)

Renseigner dans `.env` :

```
TWILIO_SID=AC...
TWILIO_AUTH_TOKEN=...
TWILIO_FROM_NUMBER=+33...
```

Commande rappels : `php artisan appointments:send-reminders` (options : `--sms`, `--push` pour Web Push staff)

### 7. Laravel Forge

- Create Server → PHP 8.2
- Create Site → point vers le repo
- Enable Queue (1 worker)
- Env: DB_*, MAIL_*, APP_URL, TWILIO_* (optionnel)
