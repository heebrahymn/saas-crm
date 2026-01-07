<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Task::class);

        $query = Task::with('assignedUser');

        // Search and filtering
        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('due_date_from')) {
            $query->where('due_date', '>=', $request->due_date_from);
        }

        if ($request->filled('due_date_to')) {
            $query->where('due_date', '<=', $request->due_date_to);
        }

        // Only show tasks assigned to current user if not admin/manager
        if (!$request->user()->hasRole('admin') && !$request->user()->hasRole('manager')) {
            $query->where('assigned_to', $request->user()->id);
        }

        $tasks = $query->orderBy('due_date', 'asc')
                      ->orderBy('created_at', 'desc')
                      ->paginate($request->per_page ?? 15);

        return response()->json([
            'tasks' => $tasks,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Task::class);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:users,id',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'status' => 'nullable|in:pending,in_progress,completed,cancelled',
            'related_to_type' => 'nullable|in:contact,lead,deal',
            'related_to_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();
        
        // Only allow assignment to self if not admin/manager and not assigning to self
        if (!$request->user()->hasRole('admin') && !$request->user()->hasRole('manager')) {
            if ($validated['assigned_to'] !== $request->user()->id) {
                return response()->json([
                    'message' => 'You can only assign tasks to yourself'
                ], 403);
            }
        }

        $task = Task::create(array_merge(
            $validated,
            ['company_id' => $request->user()->company_id]
        ));

        return response()->json([
            'message' => 'Task created successfully',
            'task' => $task->load('assignedUser'),
        ], 201);
    }

    public function show(Request $request, Task $task)
    {
        $this->authorize('view', $task);

        return response()->json([
            'task' => $task->load('assignedUser'),
        ]);
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'sometimes|exists:users,id',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'status' => 'nullable|in:pending,in_progress,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();
        
        // Only allow assignment to self if not admin/manager and not assigning to self
        if (!$request->user()->hasRole('admin') && !$request->user()->hasRole('manager')) {
            if (isset($validated['assigned_to']) && $validated['assigned_to'] !== $request->user()->id) {
                return response()->json([
                    'message' => 'You can only assign tasks to yourself'
                ], 403);
            }
        }

        $task->update($validated);

        return response()->json([
            'message' => 'Task updated successfully',
            'task' => $task->load('assignedUser'),
        ]);
    }

    public function destroy(Request $request, Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully',
        ]);
    }

    public function markComplete(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $task->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Task marked as completed',
            'task' => $task->load('assignedUser'),
        ]);
    }

    public function markIncomplete(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $task->update([
            'status' => 'pending',
            'completed_at' => null,
        ]);

        return response()->json([
            'message' => 'Task marked as pending',
            'task' => $task->load('assignedUser'),
        ]);
    }
}