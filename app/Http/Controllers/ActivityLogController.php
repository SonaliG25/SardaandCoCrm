<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::query();

        // Filter by user
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by module
        if ($request->module) {
            $query->where('module', $request->module);
        }

        // Filter by action
        if ($request->action) {
            $query->where('action', $request->action);
        }

        // Filter by date range
        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Search by description
        if ($request->search) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(50);
        
        $users = ActivityLog::select('user_id', 'user_name')->distinct()->get();
        $modules = ActivityLog::select('module')->distinct()->get();
        $actions = ActivityLog::select('action')->distinct()->get();

        return view('activity-logs.index', compact('logs', 'users', 'modules', 'actions'));
    }

    // Export logs to CSV
    public function export(Request $request)
    {
        $query = ActivityLog::query();

        if ($request->user_id) $query->where('user_id', $request->user_id);
        if ($request->module) $query->where('module', $request->module);
        if ($request->action) $query->where('action', $request->action);
        if ($request->from_date) $query->whereDate('created_at', '>=', $request->from_date);
        if ($request->to_date) $query->whereDate('created_at', '<=', $request->to_date);

        $logs = $query->orderBy('created_at', 'desc')->get();

        $filename = 'activity-logs-' . now()->format('Y-m-d-H-i-s') . '.csv';
        
        $headers = array(
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
        );

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, ['Date', 'User', 'Role', 'Action', 'Module', 'Record ID', 'Description', 'IP Address']);
            
            // Data rows
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->user_name,
                    $log->user_role,
                    $log->action,
                    $log->module,
                    $log->record_id,
                    $log->description,
                    $log->ip_address,
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}