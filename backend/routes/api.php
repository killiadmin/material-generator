<?php

use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\VideoPreviewController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VideoTranscriptController;
use Illuminate\Http\Request;

// Test
Route::get('/test', static function () {
    return response()->json([
        'message' => 'API Laravel 12 fonctionne !',
        'status' => 'success',
        'timestamp' => now()
    ]);
});

// Test cors
Route::get('/test-cors', static function (Request $request) {
    return response()->json([
        'cors_test' => 'CORS fonctionne',
        'origin' => $request->header('Origin'),
        'allowed' => true
    ]);
});

// Check
Route::get('/ping', [HealthCheckController::class, 'ping']);

// Video Transcript
Route::post('/video/transcript', [VideoTranscriptController::class, 'fetchTranscript']);
Route::post('/video/analyze', [VideoTranscriptController::class, 'analyzeVideo']);

// Video Preview
Route::post('/video/preview', [VideoPreviewController::class, 'preview']);
