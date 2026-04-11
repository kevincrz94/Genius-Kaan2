<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $databaseOk = $this->databaseIsReachable();

        return response()->json([
            'status' => $databaseOk ? 'ok' : 'degraded',
            'app' => config('app.name'),
            'environment' => app()->environment(),
            'laravel' => app()->version(),
            'php' => PHP_VERSION,
            'database' => [
                'connection' => config('database.default'),
                'ok' => $databaseOk,
            ],
            'cognifit' => [
                'configured' => filled(config('services.cognifit.api_key'))
                    && filled(config('services.cognifit.secret_key'))
                    && filled(config('services.cognifit.client_id')),
                'client_id_present' => filled(config('services.cognifit.client_id')),
            ],
        ], $databaseOk ? 200 : 503);
    }

    private function databaseIsReachable(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
