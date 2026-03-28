<?php

namespace App\Services;

use App\Models\Notifikasi;
use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotifikasiService
{
    /**
     * Kirim notifikasi ke satu user.
     */
    public function kirim(int $idUser, string $tipe, string $judul, string $pesan, array $data = []): void
    {
        try {
            Notifikasi::create([
                'id_user' => $idUser,
                'tipe'    => $tipe,
                'judul'   => $judul,
                'pesan'   => $pesan,
                'data'    => $data,
                'is_read' => false,
            ]);

            $this->sendPushNotification($idUser, $judul, $pesan, $data);
        } catch (\Exception $e) {
            Log::error('NotifikasiService::kirim gagal: ' . $e->getMessage());
        }
    }

    /**
     * Kirim notifikasi ke semua staff aktif (broadcast pengumuman).
     */
    public function kirimBroadcast(string $tipe, string $judul, string $pesan, array $data = []): void
    {
        $users = User::where('status_aktif', 1)
            ->whereDoesntHave('roles', function ($q) {
                $q->whereIn('nama_role', ['super_admin', 'hrd']);
            })
            ->pluck('id');

        foreach ($users as $idUser) {
            $this->kirim($idUser, $tipe, $judul, $pesan, $data);
        }
    }

    /**
     * Kirim ke semua user dengan role tertentu (contoh: HRD, Manajer).
     */
    public function kirimKeRole(string $namaRole, string $tipe, string $judul, string $pesan, array $data = []): void
    {
        $users = User::where('status_aktif', 1)
            ->whereHas('roles', function ($q) use ($namaRole) {
                $q->where('nama_role', $namaRole);
            })
            ->pluck('id');

        foreach ($users as $idUser) {
            $this->kirim($idUser, $tipe, $judul, $pesan, $data);
        }
    }

    /**
     * Kirim push notification ke device FCM user via FCM HTTP v1 API.
     */
    private function sendPushNotification(int $idUser, string $judul, string $pesan, array $data = []): void
    {
        $tokens = DeviceToken::where('id_user', $idUser)->pluck('fcm_token');
        if ($tokens->isEmpty()) return;

        $accessToken = $this->getFcmAccessToken();
        if (!$accessToken) return;

        $projectId = env('FIREBASE_PROJECT_ID');

        foreach ($tokens as $token) {
            try {
                \Illuminate\Support\Facades\Http::withToken($accessToken)
                    ->withoutVerifying()
                    ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                        'message' => [
                            'token'        => $token,
                            'notification' => ['title' => $judul, 'body' => $pesan],
                            'data'         => array_map('strval', $data),
                            'android'      => [
                                'notification' => [
                                    'channel_id'   => 'hris_channel',
                                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                                ],
                            ],
                        ],
                    ]);
            } catch (\Exception $e) {
                Log::error("FCM gagal untuk token $token: " . $e->getMessage());
            }
        }
    }

    /**
     * Ambil access token Google OAuth2 menggunakan Service Account JWT.
     */
    private function getFcmAccessToken(): ?string
    {
        try {
            $credPath    = base_path(env('FIREBASE_CREDENTIALS'));
            $credentials = json_decode(file_get_contents($credPath), true);
            $now = time();

            $header  = rtrim(strtr(base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'])), '+/', '-_'), '=');
            $payload = rtrim(strtr(base64_encode(json_encode([
                'iss'   => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud'   => 'https://oauth2.googleapis.com/token',
                'iat'   => $now,
                'exp'   => $now + 3600,
            ])), '+/', '-_'), '=');

            openssl_sign("$header.$payload", $sig, $credentials['private_key'], OPENSSL_ALGO_SHA256);
            $jwt = "$header.$payload." . rtrim(strtr(base64_encode($sig), '+/', '-_'), '=');

            $resp = \Illuminate\Support\Facades\Http::asForm()->withoutVerifying()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            return $resp->json('access_token');
        } catch (\Exception $e) {
            Log::error('FCM Token error: ' . $e->getMessage());
            return null;
        }
    }
}
