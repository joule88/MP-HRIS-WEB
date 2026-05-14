<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Services\NotifikasiService;

class BroadcastNotifikasiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tipe;
    public $judul;
    public $pesan;
    public $data;

    /**
     * Create a new job instance.
     */
    public function __construct(string $tipe, string $judul, string $pesan, array $data = [])
    {
        $this->tipe = $tipe;
        $this->judul = $judul;
        $this->pesan = $pesan;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(NotifikasiService $notifikasiService): void
    {
        $users = User::where('status_aktif', 1)
            ->whereDoesntHave('roles', function ($q) {
                $q->whereIn('nama_role', ['super_admin']);
            })
            ->pluck('id');

        foreach ($users as $idUser) {
            $notifikasiService->kirim($idUser, $this->tipe, $this->judul, $this->pesan, $this->data);
        }
    }
}
