<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DealController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Deal::class);

        $query = Deal::with('contact');

        // Search and filtering
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhereHas('contact', function ($contactQuery) use ($request) {
                      $contactQuery->where('first_name', 'like', "%{$request->search}%")
                                  ->orWhere('last_name', 'like', "%{$request->search}%")
                                  ->orWhere('email', 'like', "%{$request->search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('pipeline_stage')) {
            $query->where('pipeline_stage', $request->pipeline_stage);
        }

        $deals = $query->orderBy('created_at', 'desc')
                      ->paginate($request->per_page ?? 15);

        return response()->json([
            'deals' => $deals,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Deal::class);

        $validator = Validator::make($request->all(), [
            'contact_id' => 'required|exists:contacts,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'value' => 'required|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'status' => 'nullable|in:proposed,negotiating,approved,closed_won,closed_lost',
            'probability' => 'nullable|integer|min:0|max:100',
            'estimated_close_date' => 'nullable|date',
            'actual_close_date' => 'nullable|date',
            'pipeline_stage' => 'nullable|string|max:50',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();
        
        // Only assign to self if not admin/manager
        if (!$request->user()->hasRole('admin') && !$request->user()->hasRole('manager')) {
            $validated['assigned_to'] = $request->user()->id;
        }

        $deal = Deal::create(array_merge(
            $validated,
            ['company_id' => $request->user()->company_id]
        ));

        return response()->json([
            'message' => 'Deal created successfully',
            'deal' => $deal->load('contact'),
        ], 201);
    }

    public function show(Request $request, Deal $deal)
    {
        $this->authorize('view', $deal);

        return response()->json([
            'deal' => $deal->load('contact'),
        ]);
    }

    public function update(Request $request, Deal $deal)
    {
        $this->authorize('update', $deal);

        $validator = Validator::make($request->all(), [
            'contact_id' => 'sometimes|exists:contacts,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'value' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'status' => 'nullable|in:proposed,negotiating,approved,closed_won,closed_lost',
            'probability' => 'nullable|integer|min:0|max:100',
            'estimated_close_date' => 'nullable|date',
            'actual_close_date' => 'nullable|date',
            'pipeline_stage' => 'nullable|string|max:50',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();
        
        // Only allow assignment to self if not admin/manager
        if (!$request->user()->hasRole('admin') && !$request->user()->hasRole('manager')) {
            if (isset($validated['assigned_to']) && $validated['assigned_to'] !== $request->user()->id) {
                unset($validated['assigned_to']);
            }
        }

        $deal->update($validated);

        return response()->json([
            'message' => 'Deal updated successfully',
            'deal' => $deal->load('contact'),
        ]);
    }

    public function destroy(Request $request, Deal $deal)
    {
        $this->authorize('delete', $deal);

        $deal->delete();

        return response()->json([
            'message' => 'Deal deleted successfully',
        ]);
    }

    public function close(Request $request, Deal $deal)
    {
        $this->authorize('update', $deal);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:closed_won,closed_lost',
            'actual_close_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $deal->update(array_merge(
            $validator->validated(),
            ['pipeline_stage' => 'closed']
        ));

        return response()->json([
            'message' => 'Deal closed successfully',
            'deal' => $deal->load('contact'),
        ]);
    }
}