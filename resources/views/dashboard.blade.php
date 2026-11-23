@extends('layouts.app')

@section('title', 'Dashboard')
@section('heading', 'Ministry Back-Office Overview')

@section('content')
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="rounded-lg bg-white p-5 border border-slate-200 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Active Staff Accounts</p>
        <p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['active_users'] }} <span class="text-sm font-normal text-slate-500">/ {{ $stats['total_users'] }}</span></p>
    </div>
    <div class="rounded-lg bg-white p-5 border border-slate-200 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Configured Kogi LGAs</p>
        <p class="mt-2 text-2xl font-bold text-emerald-700">{{ $stats['lgas'] }}</p>
    </div>
    <div class="rounded-lg bg-white p-5 border border-slate-200 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Asset Categories &amp; License Types</p>
        <p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['asset_categories'] }} <span class="text-sm font-normal text-slate-500">/ {{ $stats['license_types'] }}</span></p>
    </div>
    <div class="rounded-lg bg-white p-5 border border-slate-200 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Audit Events Today</p>
        <p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['audit_events_today'] }}</p>
    </div>
</div>

<div class="rounded-lg bg-white border border-slate-200 shadow-sm p-5">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-base font-semibold text-slate-900">Phase 1 — Foundation Active</h3>
            <p class="text-sm text-slate-600 mt-1">
                Role-based access control, immutable audit logging, and Kogi State system configuration tables are live.
            </p>
        </div>
        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">
            {{ auth()->user()->primaryRoleLabel() }}
        </span>
    </div>
</div>

@can('viewAny', \App\Models\AuditLog::class)
<div class="rounded-lg bg-white border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
        <h3 class="text-base font-semibold text-slate-900">Recent Audit Activity</h3>
        <a href="{{ route('admin.audit-logs.index') }}" class="text-sm font-medium text-emerald-700 hover:text-emerald-800">
            View Full Audit Trail →
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">Timestamp</th>
                    <th class="px-4 py-3">Officer</th>
                    <th class="px-4 py-3">Action</th>
                    <th class="px-4 py-3">Entity</th>
                    <th class="px-4 py-3">IP Address</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($recentAudits as $log)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 whitespace-nowrap text-slate-600">{{ \App\Support\Format::datetime($log->created_at) }}</td>
                    <td class="px-4 py-3 font-medium text-slate-900">{{ $log->user?->name ?? 'System' }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $log->action->badgeColor() }}">
                            {{ $log->action->label() }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-slate-700">{{ $log->shortEntityType() }} @if($log->entity_id)#{{ $log->entity_id }}@endif</td>
                    <td class="px-4 py-3 text-slate-500 font-mono text-xs">{{ $log->ip_address ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center text-slate-500">No audit entries recorded yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endcan
@endsection
