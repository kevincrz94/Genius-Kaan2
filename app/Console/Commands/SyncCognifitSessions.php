<?php

namespace App\Console\Commands;

use App\Services\CognifitSessionSyncService;
use Illuminate\Console\Command;

class SyncCognifitSessions extends Command
{
    protected $signature = 'cognifit:sync-sessions {--limit=50 : Maximo de sesiones pendientes a revisar}';

    protected $description = 'Sincroniza sesiones de CogniFit que quedaron pendientes de procesamiento.';

    public function handle(CognifitSessionSyncService $syncService): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $summary = $syncService->syncDueSessions($limit);

        $this->info(sprintf(
            'CogniFit sync: %d revisadas, %d sincronizadas, %d diferidas, %d fallidas.',
            $summary['checked'],
            $summary['synced'],
            $summary['delayed'],
            $summary['failed'],
        ));

        return self::SUCCESS;
    }
}
