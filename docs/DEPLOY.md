# Guide de déploiement — Healthcare Balance

## Prérequis

- PHP 8.2+
- Composer
- Node.js 18+ / npm
- MariaDB ou MySQL
- Queue worker (supervisor ou systemd)

## Variables d'environnement (.env)

En production, configurer :

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=healthcare
DB_USERNAME=...
DB_PASSWORD=...

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
# ... config SMTP pour e-mails et notifications
```

## Commandes de déploiement

```bash
# 1. Installer les dépendances
composer install --optimize-autoloader --no-dev

# 2. Copier et configurer .env
cp .env.example .env
# Éditer .env avec les vraies valeurs
php artisan key:generate

# 3. Migrations
php artisan migrate --force

# 4. Frontend
npm install
npm run build

# 5. Optimisation (cache)
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

## Queue worker

Les notifications (ex. changement de statut de salle) passent par la queue. Lancer le worker :

```bash
php artisan queue:work --tries=3 --timeout=90
```

Recommandé : utiliser **Supervisor** pour garder le worker actif.

Exemple `/etc/supervisor/conf.d/healthcare-worker.conf` :

```ini
[program:healthcare-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /chemin/vers/projet/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/chemin/vers/projet/storage/logs/worker.log
stopwaitsecs=3600
```

Puis : `supervisorctl reread && supervisorctl update && supervisorctl start healthcare-worker:*`

## Vérifications post-déploiement

- [ ] `php artisan migrate --pretend` → rien à migrer
- [ ] `php artisan config:cache` → pas d'erreur
- [ ] Accès à `/login` et authentification
- [ ] Cabinet live (`/live-cabinet`) se rafraîchit (polling 15s)
- [ ] Notifications (changement statut salle) reçues par les docteurs
- [ ] Réservation en ligne publique accessible
- [ ] Rapports par salle / prestation et exports PDF/Excel

## Évolutions possibles

- **WebSockets** : remplacer le polling du cabinet live par Laravel Echo + Pusher ou Soketi pour du temps réel.
- **Redis** : utiliser Redis pour cache, sessions et queue en production.
- **Backup** : planifier des sauvegardes base + storage (fiches patients, factures PDF).
