<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MonthlyReportReadyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $month
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $label = \Carbon\Carbon::createFromFormat('Y-m', $this->month)->locale('fr')->translatedFormat('F Y');

        return [
            'type' => 'monthly_report',
            'message' => "Rapport mensuel détaillé disponible : {$label}",
            'url' => route('reports.monthly.download', ['month' => $this->month]),
        ];
    }
}
