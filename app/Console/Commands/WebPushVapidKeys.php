<?php

namespace App\Console\Commands;

use Minishlink\WebPush\VAPID;
use Illuminate\Console\Command;

class WebPushVapidKeys extends Command
{
    protected $signature = 'webpush:vapid';

    protected $description = 'Génère les clés VAPID pour Web Push';

    public function handle(): int
    {
        $keys = VAPID::createVapidKeys();

        $this->info('Ajoutez ces lignes à votre fichier .env :');
        $this->line('');
        $this->line('VAPID_PUBLIC_KEY=' . $keys['publicKey']);
        $this->line('VAPID_PRIVATE_KEY=' . $keys['privateKey']);
        $this->line('');

        return 0;
    }
}
