<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of activity logs.
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with('user');

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sort
        $query->orderBy('created_at', 'desc');

        $activityLogs = $query->paginate(50)->withQueryString();

        // Get users and actions for filter dropdowns
        $users = User::orderBy('name')->get();
        $actions = ActivityLog::distinct('action')->pluck('action')->sort();

        return view('admin.activity-logs.index', [
            'activityLogs' => $activityLogs,
            'users' => $users,
            'actions' => $actions,
        ]);
    }

    /**
     * Export activity logs as CSV.
     */
    public function export(Request $request)
    {
        $query = ActivityLog::with('user');

        // Apply same filters as index
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activityLogs = $query->orderBy('created_at', 'desc')->get();

        $filename = 'activity_logs_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($activityLogs) {
            $file = fopen('php://output', 'w');
            
            // CSV header
            fputcsv($file, [
                'ID',
                'ユーザー名',
                'ユーザーメール',
                'アクション',
                '対象タイプ',
                '対象ID',
                'IPアドレス',
                'ユーザーエージェント',
                '作成日時',
            ]);

            // CSV data
            foreach ($activityLogs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->user->name ?? '',
                    $log->user->email ?? '',
                    $log->action,
                    $log->target_type ?? '',
                    $log->target_id ?? '',
                    $log->ip_address ?? '',
                    $log->user_agent ?? '',
                    $log->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
