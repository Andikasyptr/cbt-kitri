<?php

namespace App\Providers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Paksa HTTPS di produksi (Render berada di belakang proxy HTTPS)
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Nama hari dan bulan berbahasa Indonesia (dipakai translatedFormat)
        Carbon::setLocale(config('app.locale'));
    }
}