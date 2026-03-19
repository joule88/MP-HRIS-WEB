<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="MPG HRIS API",
 *      description="API Documentation"
 * )
 * @OA\Server(
 *      url="http://localhost/api",
 *      description="API Server"
 * )
 */
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();
