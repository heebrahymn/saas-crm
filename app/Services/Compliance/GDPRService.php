<?php

namespace App\Services\Compliance;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GDPRService
{
    public function exportUserData(User $user): array
    {
        $data = [
            'user' => $user->toArray(),
            'user_role' => $user->userRole?->toArray(),
            'contacts' => $user->company->contacts->toArray(),
            'leads' => $user->company->leads->toArray(),
            'deals' => $user->company->deals->toArray(),
            'tasks' => $user->company->tasks->toArray(),
            'invitations' => $user->invitations->toArray(),
        ];

        // Remove sensitive data
        unset($data['user']['password']);
        unset($data['user']['remember_token']);

        return $data;
    }

    public function generateDataExportFile(User $user): string
    {
        $data = $this->exportUserData($user);
        $filename = 'gdpr-export-' . $user->id . '-' . now()->format('Y-m-d-H-i-s') . '.json';
        $filepath = storage_path('app/gdpr/' . $filename);

        // Ensure directory exists
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT));

        return $filepath;
    }

    public function deleteUserAccount(User $user): bool
    {
        // Start transaction
        \DB::beginTransaction();

        try {
            // Anonymize user data instead of deleting
            $user->update([
                'name' => 'Anonymized User',
                'email' => 'anonymized-' . $user->id . '@deleted-user.com',
                'password' => bcrypt(Str::random(16)),
                'phone' => null,
                'job_title' => null,
                'bio' => null,
                'is_active' => false,
            ]);

            // Delete user role
            $user->userRole?->delete();

            // Anonymize all related data
            $user->company->contacts()->update([
                'first_name' => 'Anonymized',
                'last_name' => 'Contact',
                'email' => 'anonymized-contact-' . Str::random(8) . '@deleted-user.com',
            ]);

            $user->company->leads()->update([
                'title' => 'Anonymized Lead',
                'description' => 'Anonymized lead description',
            ]);

            $user->company->deals()->update([
                'title' => 'Anonymized Deal',
                'description' => 'Anonymized deal description',
                'value' => 0,
            ]);

            $user->company->tasks()->update([
                'title' => 'Anonymized Task',
                'description' => 'Anonymized task description',
            ]);

            // Log the deletion
            \Log::info('GDPR data deletion completed', [
                'user_id' => $user->id,
                'company_id' => $user->company_id,
            ]);

            \DB::commit();

            return true;
        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('GDPR data deletion failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function consentTracking(string $userId, string $purpose, bool $granted): bool
    {
        $consentRecord = [
            'user_id' => $userId,
            'purpose' => $purpose,
            'granted' => $granted,
            'timestamp' => now()->toISOString(),
        ];

        $consents = json_decode(Storage::get('gdpr/consents.json'), true) ?: [];
        $consents[] = $consentRecord;
        
        Storage::put('gdpr/consents.json', json_encode($consents, JSON_PRETTY_PRINT));

        return true;
    }

    public function hasConsent(string $userId, string $purpose): bool
    {
        $consents = json_decode(Storage::get('gdpr/consents.json'), true) ?: [];
        
        $userConsents = array_filter($consents, function ($consent) use ($userId, $purpose) {
            return $consent['user_id'] === $userId && $consent['purpose'] === $purpose;
        });

        if (empty($userConsents)) {
            return false;
        }

        $latestConsent = end($userConsents);
        return $latestConsent['granted'];
    }
}