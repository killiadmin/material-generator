<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VideoTranscriptController;

Route::post('/video/transcript', [VideoTranscriptController::class, 'fetchTranscript']);
Route::post('/video/analyze', [VideoTranscriptController::class, 'analyzeVideo']);
