<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of system activity and audit logs.
     * Accessible only by owners/administrators.
     */
    public function index(Request $request): View
    {
        // Security check: Only store owners/admins can inspect audit logs
        abort_if(! $request->user()->isOwner(), 403, 'عذراً، سجل العمليات والتدقيق متاح فقط لمالك النظام.');

        $filters = [
            'user_id' => $request->input('user_id'),
            'action' => $request->input('action'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'search' => $request->input('search'),
        ];

        $query = ActivityLog::with('user')->filter($filters)->latest('id');

        $logs = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => ActivityLog::count(),
            'today' => ActivityLog::whereDate('created_at', today())->count(),
            'sales' => ActivityLog::whereIn('action', ['sale_created', 'sale_cancelled'])->count(),
            'returns' => ActivityLog::whereIn('action', ['return_created', 'return_cancelled'])->count(),
            'inventory' => ActivityLog::whereIn('action', ['stock_adjusted', 'product_created', 'product_updated'])->count(),
        ];

        $users = User::orderBy('name')->get(['id', 'name', 'username']);
        $actions = ActivityLogService::ACTION_LABELS;

        return view('activity-logs.index', compact('logs', 'filters', 'stats', 'users', 'actions'));
    }

    /**
     * Display the specified activity log detail.
     */
    public function show(Request $request, ActivityLog $activityLog): View
    {
        abort_if(! $request->user()->isOwner(), 403, 'عذراً، سجل العمليات والتدقيق متاح فقط لمالك النظام.');

        $activityLog->load('user');

        return view('activity-logs.show', compact('activityLog'));
    }
}

