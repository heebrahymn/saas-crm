<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    public function show(Request $request)
    {
        $company = $request->attributes->get('tenant');
        
        return response()->json([
            'company' => $company,
        ]);
    }

    public function update(Request $request)
    {
        $company = $request->attributes->get('tenant');
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:companies,email,' . $company->id,
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'settings' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $company->update($validator->validated());

        return response()->json([
            'message' => 'Company updated successfully',
            'company' => $company,
        ]);
    }

    public function getSettings(Request $request)
    {
        $company = $request->attributes->get('tenant');
        
        return response()->json([
            'settings' => $company->settings,
        ]);
    }

    public function updateSettings(Request $request)
    {
        $company = $request->attributes->get('tenant');
        
        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $company->update([
            'settings' => array_merge($company->settings ?? [], $request->settings),
        ]);

        return response()->json([
            'message' => 'Company settings updated successfully',
            'settings' => $company->settings,
        ]);
    }
}