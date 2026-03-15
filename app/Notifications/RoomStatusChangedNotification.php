<?php

namespace App\Notifications;

use App\Models\Room;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class RoomStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Room $room,
        public string $oldStatus,
        public string $newStatus,
        public ?string $userName = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $labels = Room::STATUSES;
        $label = $labels[$this->newStatus] ?? $this->newStatus;
        $by = $this->userName ? " par {$this->userName}" : '';

        return [
            'type' => 'room_status',
            'message' => "Salle {$this->room->name} : {$label}{$by}",
            'url' => route('live-cabinet.index'),
        ];
    }
}
