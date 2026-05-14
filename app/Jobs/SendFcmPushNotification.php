<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\NotifikasiService;

class SendFcmPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $idUser;
    public $judul;
    public $pesan;
    public $data;

    /**
     * Create a new job instance.
     */
    public function __construct(int $idUser, string $judul, string $pesan, array $data = [])
    {
        $this->idUser = $idUser;
        $this->judul = $judul;
        $this->pesan = $pesan;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(NotifikasiService $notifikasiService): void
    {
        $notifikasiService->sendPushNotification($this->idUser, $this->judul, $this->pesan, $this->data);
    }
}
