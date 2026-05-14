<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SuratIzinUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $idUser,
        public int $idSurat,
        public string $statusSurat,
        public int $tahap,
        public string $message,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.' . $this->idUser)];
    }

    public function broadcastAs(): string
    {
        return 'SuratIzinUpdated';
    }
}
