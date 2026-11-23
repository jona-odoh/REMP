<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', AuditLog::class);

        $filters = $request->only(['search', 'action', 'user_id', 'entity_type', 'date_from', 'date_to']);

        $logs = AuditLog::query()
            ->with('user')
            ->filter($filters)
            ->latest('created_at')
            ->paginate(25)
            ->withQueryString();

        $entityTypes = AuditLog::query()
            ->whereNotNull('entity_type')
            ->distinct()
            ->orderBy('entity_type')
            ->pluck('entity_type');

        return view('admin.audit-logs.index', [
            'logs' => $logs,
            'actions' => AuditAction::cases(),
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
            'entityTypes' => $entityTypes,
            'filters' => $filters,
        ]);
    }

    public function show(AuditLog $auditLog): View
    {
        $this->authorize('view', $auditLog);

        return view('admin.audit-logs.show', [
            'log' => $auditLog->load('user'),
        ]);
    }
}
