<?php

use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\VideoPreviewController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VideoTranscriptController;

// Check
Route::get('/ping', [HealthCheckController::class, 'ping']);

// Video Transcript
Route::post('/video/transcript', [VideoTranscriptController::class, 'fetchTranscript']);
Route::post('/video/analyze', [VideoTranscriptController::class, 'analyzeVideo'])->middleware('throttle:transcript');

// Video Preview
Route::post('/video/preview', [VideoPreviewController::class, 'preview']);
