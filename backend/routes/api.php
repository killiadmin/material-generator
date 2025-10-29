<?php

use App\Http\Controllers\HealthCheckController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VideoTranscriptController;

Route::get('/ping', [HealthCheckController::class, 'ping']);
Route::post('/video/transcript', [VideoTranscriptController::class, 'fetchTranscript']);
Route::post('/video/analyze', [VideoTranscriptController::class, 'analyzeVideo']);
