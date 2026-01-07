<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Lead::class);

        $query = Lead::with('contact');

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

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('pipeline_stage')) {
            $query->where('pipeline_stage', $request->pipeline_stage);
        }

        $leads = $query->orderBy('created_at', 'desc')
                      ->paginate($request->per_page ?? 15);

        return response()->json([
            'leads' => $leads,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Lead::class);

        $validator = Validator::make($request->all(), [
            'contact_id' => 'required|exists:contacts,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'value' => 'nullable|numeric|min:0',
            'source' => 'nullable|string|max:50',
            'status' => 'nullable|in:new,contacted,qualified,proposal,scheduled,closed_won,closed_lost',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'estimated_close_date' => 'nullable|date',
            'pipeline_stage' => 'nullable|string|max:50',
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

        $lead = Lead::create(array_merge(
            $validated,
            ['company_id' => $request->user()->company_id]
        ));

        return response()->json([
            'message' => 'Lead created successfully',
            'lead' => $lead->load('contact'),
        ], 201);
    }

    public function show(Request $request, Lead $lead)
    {
        $this->authorize('view', $lead);

        return response()->json([
            'lead' => $lead->load('contact'),
        ]);
    }

    public function update(Request $request, Lead $lead)
    {
        $this->authorize('update', $lead);

        $validator = Validator::make($request->all(), [
            'contact_id' => 'sometimes|exists:contacts,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'value' => 'nullable|numeric|min:0',
            'source' => 'nullable|string|max:50',
            'status' => 'nullable|in:new,contacted,qualified,proposal,scheduled,closed_won,closed_lost',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'estimated_close_date' => 'nullable|date',
            'pipeline_stage' => 'nullable|string|max:50',
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

        $lead->update($validated);

        return response()->json([
            'message' => 'Lead updated successfully',
            'lead' => $lead->load('contact'),
        ]);
    }

    public function destroy(Request $request, Lead $lead)
    {
        $this->authorize('delete', $lead);

        $lead->delete();

        return response()->json([
            'message' => 'Lead deleted successfully',
        ]);
    }

    public function convertToDeal(Request $request, Lead $lead)
    {
        $this->authorize('update', $lead);

        // Create deal from lead
        $deal = \App\Models\Deal::create([
            'company_id' => $lead->company_id,
            'contact_id' => $lead->contact_id,
            'lead_id' => $lead->id,
            'title' => $lead->title,
            'description' => $lead->description,
            'value' => $lead->value,
            'currency' => 'USD',
            'status' => 'proposed',
            'probability' => 50,
            'estimated_close_date' => $lead->estimated_close_date,
            'assigned_to' => $lead->assigned_to,
            'pipeline_stage' => 'proposed',
        ]);

        // Update lead status to converted
        $lead->update(['status' => 'converted']);

        return response()->json([
            'message' => 'Lead converted to deal successfully',
            'deal' => $deal->load('contact'),
            'lead' => $lead,
        ]);
    }
}