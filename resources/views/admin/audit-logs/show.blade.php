@extends('layouts.app')

@section('title', 'Audit Event #'.$log->id)
@section('heading', 'Audit Log Detail #'.$log->id)

@section('content')
<div class="space-y-4">
    <a href="{{ route('admin.audit-logs.index') }}" class="text-sm font-medium text-emerald-700 hover:text-emerald-900">← Back to Audit Trail</a>

    <div class="rounded-lg bg-white border border-slate-200 shadow-sm p-5 grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
        <div>
            <p class="text-xs uppercase text-slate-500">Action</p>
            <p class="mt-1"><span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $log->action->badgeColor() }}">{{ $log->action->label() }}</span></p>
        </div>
        <div>
            <p class="text-xs uppercase text-slate-500">Officer</p>
            <p class="mt-1 font-medium text-slate-900">{{ $log->user?->name ?? 'System' }} ({{ $log->user?->email ?? '—' }})</p>
        </div>
        <div>
            <p class="text-xs uppercase text-slate-500">Timestamp</p>
            <p class="mt-1 font-medium text-slate-900">{{ \App\Support\Format::datetime($log->created_at) }}</p>
        </div>
        <div>
            <p class="text-xs uppercase text-slate-500">Entity</p>
            <p class="mt-1 font-mono text-xs text-slate-800">{{ $log->entity_type ?? 'N/A' }} @if($log->entity_id)#{{ $log->entity_id }}@endif</p>
        </div>
        <div>
            <p class="text-xs uppercase text-slate-500">IP Address</p>
            <p class="mt-1 font-mono text-xs text-slate-800">{{ $log->ip_address ?? '—' }}</p>
        </div>
        <div>
            <p class="text-xs uppercase text-slate-500">User Agent</p>
            <p class="mt-1 text-xs text-slate-600 break-all">{{ $log->user_agent ?? '—' }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="rounded-lg bg-white border border-slate-200 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-slate-900 mb-2">Before Value (JSON)</h3>
            <pre class="rounded bg-slate-900 text-slate-100 p-3 text-xs overflow-x-auto">{{ $log->before_value ? json_encode($log->before_value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : 'null' }}</pre>
        </div>
        <div class="rounded-lg bg-white border border-slate-200 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-slate-900 mb-2">After Value (JSON)</h3>
            <pre class="rounded bg-slate-900 text-slate-100 p-3 text-xs overflow-x-auto">{{ $log->after_value ? json_encode($log->after_value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : 'null' }}</pre>
        </div>
    </div>

    @if($log->metadata)
    <div class="rounded-lg bg-white border border-slate-200 shadow-sm p-5">
        <h3 class="text-sm font-semibold text-slate-900 mb-2">Metadata</h3>
        <pre class="rounded bg-slate-900 text-slate-100 p-3 text-xs overflow-x-auto">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
    </div>
    @endif
</div>
@endsection
