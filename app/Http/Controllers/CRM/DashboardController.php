<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Task;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->user()->company_id;

        $stats = [
            'contacts' => Contact::count(),
            'leads' => Lead::count(),
            'deals' => Deal::count(),
            'tasks' => Task::count(),
        ];

        // Recent activity
        $recentContacts = Contact::orderBy('created_at', 'desc')->limit(5)->get();
        $recentLeads = Lead::with('contact')->orderBy('created_at', 'desc')->limit(5)->get();
        $recentDeals = Deal::with('contact')->orderBy('created_at', 'desc')->limit(5)->get();
        $recentTasks = Task::with('assignedUser')->orderBy('created_at', 'desc')->limit(5)->get();

        // Upcoming tasks for current user
        $upcomingTasks = Task::where('assigned_to', $request->user()->id)
                           ->where('due_date', '>=', now())
                           ->where('status', '!=', 'completed')
                           ->orderBy('due_date', 'asc')
                           ->limit(10)
                           ->get();

        // Deal pipeline
        $pipeline = Deal::selectRaw('pipeline_stage, COUNT(*) as count, SUM(value) as total_value')
                       ->groupBy('pipeline_stage')
                       ->get();

        // Lead conversion rates
        $totalLeads = Lead::count();
        $convertedLeads = Lead::where('status', 'closed_won')->count();
        $conversionRate = $totalLeads > 0 ? ($convertedLeads / $totalLeads) * 100 : 0;

        return response()->json([
            'stats' => $stats,
            'recent' => [
                'contacts' => $recentContacts,
                'leads' => $recentLeads,
                'deals' => $recentDeals,
                'tasks' => $recentTasks,
            ],
            'upcoming_tasks' => $upcomingTasks,
            'pipeline' => $pipeline,
            'conversion_rate' => $conversionRate,
        ]);
    }
}