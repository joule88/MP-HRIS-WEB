<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PengajuanBaru implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $tipe,
        public string $namaPegawai,
        public string $detail,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('hrd')];
    }

    public function broadcastAs(): string
    {
        return 'PengajuanBaru';
    }
}
