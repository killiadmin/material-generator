<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\VideoTranscriptInterface;
use App\Services\VideoTranscriptService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

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
    public function boot()
    {
        RateLimiter::for('transcript', function (Request $request) {
            return Limit::perDay(3)->by($request->ip());
        });
    }
}
