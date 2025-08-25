<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Display a listing of the audit logs.
     */
    public function index(Request $request)
    {
        $query = AuditLog::with('user');

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', 'like', '%' . $request->action . '%');
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(20);
        $users = \App\Models\User::all();

        return view('admin.audit-logs.index', compact('logs', 'users'));
    }

    /**
     * Display the specified audit log.
     */
    public function show(AuditLog $auditLog)
    {
        return view('admin.audit-logs.show', compact('auditLog'));
    }

    /**
     * Remove the specified audit log from storage.
     */
    public function destroy(AuditLog $auditLog)
    {
        $auditLog->delete();

        return redirect()->route('admin.audit-logs.index')
            ->with('success', 'Audit log entry deleted successfully.');
    }

    /**
     * Clear all audit logs.
     */
    public function clear()
    {
        AuditLog::truncate();

        return redirect()->route('admin.audit-logs.index')
            ->with('success', 'All audit logs cleared successfully.');
    }
}
