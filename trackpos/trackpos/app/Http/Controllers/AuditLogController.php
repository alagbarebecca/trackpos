<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->orderBy('created_at', 'desc');

        // Filter by entity type
        if ($request->entity_type) {
            $query->where('entity_type', 'like', '%' . $request->entity_type);
        }

        // Filter by action
        if ($request->action) {
            $query->where('action', $request->action);
        }

        // Filter by user
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Search in description
        if ($request->search) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $logs = $query->paginate(50);
        
        // Get filter options
        $entityTypes = AuditLog::distinct()->pluck('entity_type')->filter()->map(function($type) {
            return class_basename($type);
        })->sort()->values();

        $actions = AuditLog::distinct()->pluck('action')->sort()->values();

        return view('audit-logs.index', compact('logs', 'entityTypes', 'actions'));
    }

    public function show(AuditLog $auditLog)
    {
        return view('audit-logs.show', compact('auditLog'));
    }
}