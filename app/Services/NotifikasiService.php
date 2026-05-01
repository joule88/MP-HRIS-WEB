<?php

namespace App\Services;

use App\Events\NotifikasiCreated;
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

            $unreadCount = Notifikasi::where('id_user', $idUser)->where('is_read', false)->count();
            broadcast(new NotifikasiCreated($idUser, $judul, $pesan, $tipe, $unreadCount));

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
                $q->whereIn('nama_role', ['super_admin']);
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
     * Token yang sudah tidak valid (UNREGISTERED) otomatis dihapus dari DB.
     */
    private function sendPushNotification(int $idUser, string $judul, string $pesan, array $data = []): void
    {
        $tokens = DeviceToken::where('id_user', $idUser)->get();
        if ($tokens->isEmpty()) {
            Log::debug("[FCM] User {$idUser}: tidak ada device token.");
            return;
        }

        $accessToken = $this->getFcmAccessToken();
        if (!$accessToken) {
            Log::error("[FCM] User {$idUser}: gagal mendapat OAuth token.");
            return;
        }

        $projectId = env('FIREBASE_PROJECT_ID');

        foreach ($tokens as $deviceToken) {
            $token = $deviceToken->fcm_token;
            try {
                $response = \Illuminate\Support\Facades\Http::withToken($accessToken)
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

                if ($response->successful()) {
                    Log::info("[FCM] Berhasil kirim ke user {$idUser}, token=" . substr($token, 0, 20) . "...");
                } else {
                    $errorCode = $response->json('error.details.0.errorCode') ?? $response->json('error.status');
                    Log::warning("[FCM] Gagal kirim ke user {$idUser}: HTTP {$response->status()}, errorCode={$errorCode}");

                    // Hapus token kadaluarsa otomatis
                    if (in_array($errorCode, ['UNREGISTERED', 'INVALID_ARGUMENT'])) {
                        $deviceToken->delete();
                        Log::warning("[FCM] Token kadaluarsa dihapus untuk user {$idUser}.");
                    }
                }
            } catch (\Exception $e) {
                Log::error("[FCM] Exception untuk user {$idUser}: " . $e->getMessage());
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
