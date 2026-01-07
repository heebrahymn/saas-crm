<?php

namespace App\Http\Controllers\DataRetention;

use App\Http\Controllers\Controller;
use App\Services\DataRetention\DataRetentionService;
use Illuminate\Http\Request;

class DataRetentionController extends Controller
{
    public function __construct(private DataRetentionService $retentionService) {}

    public function policyInfo(Request $request)
    {
        $info = $this->retentionService->getRetentionPolicyInfo();
        
        return response()->json($info);
    }

    public function cleanup(Request $request)
    {
        $results = $this->retentionService->cleanupOldData();
        
        return response()->json([
            'message' => 'Data retention cleanup completed',
            'results' => $results,
        ]);
    }
}