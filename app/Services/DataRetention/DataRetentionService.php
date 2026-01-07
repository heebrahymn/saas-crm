<?php

namespace App\Services\DataRetention;

use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DataRetentionService
{
    public function cleanupOldData(): array
    {
        $results = [
            'contacts_deleted' => 0,
            'leads_deleted' => 0,
            'deals_deleted' => 0,
            'tasks_deleted' => 0,
            'errors' => [],
        ];

        $retentionConfig = config('tenancy.data_retention.policies');

        try {
            if ($retentionConfig['contacts'] ?? false) {
                $cutoffDate = Carbon::now()->subDays($retentionConfig['contacts']);
                $results['contacts_deleted'] = Contact::where('created_at', '<', $cutoffDate)
                    ->delete();
            }

            if ($retentionConfig['leads'] ?? false) {
                $cutoffDate = Carbon::now()->subDays($retentionConfig['leads']);
                $results['leads_deleted'] = Lead::where('created_at', '<', $cutoffDate)
                    ->delete();
            }

            if ($retentionConfig['deals'] ?? false) {
                $cutoffDate = Carbon::now()->subDays($retentionConfig['deals']);
                $results['deals_deleted'] = Deal::where('created_at', '<', $cutoffDate)
                    ->delete();
            }

            if ($retentionConfig['tasks'] ?? false) {
                $cutoffDate = Carbon::now()->subDays($retentionConfig['tasks']);
                $results['tasks_deleted'] = Task::where('created_at', '<', $cutoffDate)
                    ->delete();
            }

            Log::info('Data retention cleanup completed', $results);
        } catch (\Exception $e) {
            Log::error('Data retention cleanup failed', [
                'error' => $e->getMessage(),
            ]);
            $results['errors'][] = $e->getMessage();
        }

        return $results;
    }

    public function scheduleCleanupJob(): void
    {
        // This would typically be called from a scheduled command
        $this->cleanupOldData();
    }

    public function getRetentionPolicyInfo(): array
    {
        $retentionConfig = config('tenancy.data_retention.policies');
        
        return [
            'policies' => $retentionConfig,
            'next_cleanup' => Carbon::now()->addDay()->startOfDay(),
            'total_records' => [
                'contacts' => Contact::count(),
                'leads' => Lead::count(),
                'deals' => Deal::count(),
                'tasks' => Task::count(),
            ],
        ];
    }
}