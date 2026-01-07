<?php

namespace App\Console\Commands;

use App\Services\DataRetention\DataRetentionService;
use Illuminate\Console\Command;

class DataRetentionCleanupCommand extends Command
{
    protected $signature = 'data-retention:cleanup';
    protected $description = 'Clean up old data according to retention policies';

    public function handle(DataRetentionService $retentionService)
    {
        $this->info('Starting data retention cleanup...');

        $results = $retentionService->cleanupOldData();

        $this->info('Data retention cleanup completed:');
        $this->info("- Contacts deleted: {$results['contacts_deleted']}");
        $this->info("- Leads deleted: {$results['leads_deleted']}");
        $this->info("- Deals deleted: {$results['deals_deleted']}");
        $this->info("- Tasks deleted: {$results['tasks_deleted']}");

        if (!empty($results['errors'])) {
            foreach ($results['errors'] as $error) {
                $this->error("Error: {$error}");
            }
        }

        return 0;
    }
}