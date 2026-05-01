<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('hrd', function ($user) {
    return $user->roles->contains('nama_role', 'hrd') || $user->isGlobalAdmin();
});

Broadcast::channel('pengumuman', function () {
    return true;
});
