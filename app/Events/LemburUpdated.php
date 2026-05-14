<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LemburUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $idUser,
        public string $idLembur,
        public string $status,
        public string $message,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.' . $this->idUser)];
    }

    public function broadcastAs(): string
    {
        return 'LemburUpdated';
    }
}
