<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;
use App\Models\Presensi;

class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {

    }

    public function boot(): void
    {
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        \Illuminate\Pagination\Paginator::useTailwind();

        View::composer('layouts.sidebar', function ($view) {
            $pendingPresensi = Presensi::where('id_validasi', 2)->count();
            $view->with('pendingPresensi', $pendingPresensi);
        });
    }
}
