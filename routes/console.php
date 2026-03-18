<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('appointments:send-reminders')
    ->dailyAt('18:00')
    ->timezone(config('app.timezone'))
    ->withoutOverlapping();

Schedule::command('reports:monthly')
    ->monthlyOn(1, '06:00')
    ->timezone(config('app.timezone'));

// Nettoyage quotidien des logs Assistant IA (rétention selon rôle utilisateur)
Schedule::command('ai-assistant:cleanup-logs')
    ->dailyAt('03:30')
    ->timezone(config('app.timezone'))
    ->withoutOverlapping();
