<?php

namespace App\Http\Controllers\Compliance;

use App\Http\Controllers\Controller;
use App\Services\Compliance\GDPRService;
use Illuminate\Http\Request;

class GDPRController extends Controller
{
    public function __construct(private GDPRService $gdprService) {}

    public function exportData(Request $request)
    {
        $user = $request->user();
        
        $filepath = $this->gdprService->generateDataExportFile($user);
        
        return response()->download($filepath, 'gdpr-data-export.json')->deleteFileAfterSend();
    }

    public function deleteAccount(Request $request)
    {
        $user = $request->user();
        
        $success = $this->gdprService->deleteUserAccount($user);
        
        if ($success) {
            // Log out the user after deletion
            $request->user()->currentAccessToken()->delete();
            
            return response()->json([
                'message' => 'Account deleted successfully',
            ]);
        }
        
        return response()->json([
            'message' => 'Failed to delete account',
        ], 500);
    }

    public function consent(Request $request)
    {
        $request->validate([
            'purpose' => 'required|string',
            'granted' => 'required|boolean',
        ]);

        $userId = $request->user()->id;
        $purpose = $request->purpose;
        $granted = $request->granted;

        $success = $this->gdprService->consentTracking($userId, $purpose, $granted);

        return response()->json([
            'message' => $success ? 'Consent recorded' : 'Failed to record consent',
            'success' => $success,
        ]);
    }

    public function checkConsent(Request $request)
    {
        $request->validate([
            'purpose' => 'required|string',
        ]);

        $userId = $request->user()->id;
        $purpose = $request->purpose;

        $hasConsent = $this->gdprService->hasConsent($userId, $purpose);

        return response()->json([
            'has_consent' => $hasConsent,
            'purpose' => $purpose,
        ]);
    }
}