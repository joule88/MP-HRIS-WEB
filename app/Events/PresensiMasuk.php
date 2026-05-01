<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PresensiMasuk implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $namaPegawai,
        public string $jamMasuk,
        public string $status,
        public bool $verifikasiWajah,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('hrd')];
    }

    public function broadcastAs(): string
    {
        return 'PresensiMasuk';
    }
}
