@extends('layouts.app')

@section('title', 'Audit Trail')
@section('heading', 'Immutable Audit Trail (Read-Only)')

@section('content')
<form method="GET" action="{{ route('admin.audit-logs.index') }}" class="rounded-lg bg-white border border-slate-200 p-4 shadow-sm grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search officer, IP, entity..."
           aria-label="Search audit logs" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
    <select name="action" aria-label="Action filter" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <option value="">All Actions</option>
        @foreach($actions as $action)
            <option value="{{ $action->value }}" @selected(($filters['action'] ?? '') === $action->value)>{{ $action->label() }}</option>
        @endforeach
    </select>
    <select name="user_id" aria-label="Officer filter" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <option value="">All Officers</option>
        @foreach($users as $u)
            <option value="{{ $u->id }}" @selected((string) ($filters['user_id'] ?? '') === (string) $u->id)>{{ $u->name }}</option>
        @endforeach
    </select>
    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" aria-label="From date"
           class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" aria-label="To date"
           class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
    <div class="flex gap-2">
        <button type="submit" class="flex-1 rounded-md bg-slate-800 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-700">Filter</button>
        <a href="{{ route('admin.audit-logs.index') }}" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50">Reset</a>
    </div>
</form>

<div class="rounded-lg bg-white border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">Timestamp</th>
                    <th class="px-4 py-3">Officer</th>
                    <th class="px-4 py-3">Action</th>
                    <th class="px-4 py-3">Entity</th>
                    <th class="px-4 py-3">IP Address</th>
                    <th class="px-4 py-3 text-right">Details</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($logs as $log)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 whitespace-nowrap text-slate-600">{{ \App\Support\Format::datetime($log->created_at) }}</td>
                    <td class="px-4 py-3 font-medium text-slate-900">{{ $log->user?->name ?? 'System' }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $log->action->badgeColor() }}">
                            {{ $log->action->label() }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-slate-700">{{ $log->shortEntityType() }} @if($log->entity_id)#{{ $log->entity_id }}@endif</td>
                    <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $log->ip_address ?? '—' }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.audit-logs.show', $log) }}" class="text-xs font-medium text-emerald-700 hover:text-emerald-900">Inspect →</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-slate-500">No audit records found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t border-slate-200">
        {{ $logs->links() }}
    </div>
</div>
@endsection
