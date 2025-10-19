<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\VideoTranscriptInterface;
use App\Services\VideoTranscriptService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(VideoTranscriptInterface::class, VideoTranscriptService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
