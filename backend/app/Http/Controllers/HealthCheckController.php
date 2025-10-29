<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class HealthCheckController extends Controller
{
    /**
     * Method for pinging the application
     *
     * @return JsonResponse
     */
    public function ping(): JsonResponse
    {
        $db_ok = false;
        try {
            DB::connection()->getPdo();
            $db_ok = true;
        } catch (\Exception $e) {}

        return response()->json([
            'status' => $db_ok ? 'ok' : 'error',
            'database' => $db_ok ? 'connected' : 'unreachable',
            'timestamp' => now(),
        ]);
    }
}
