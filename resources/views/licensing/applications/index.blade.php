@extends('layouts.app')

@section('title', 'License Application Pipeline')
@section('heading', 'Hotel License Application & Review Queue')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3">
    <form method="GET" action="{{ route('licensing.applications.index') }}" class="flex flex-wrap items-center gap-2">
        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="App #, license #, hotel..." class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm">
        <select name="status" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm">
            <option value="">All Pipeline Stages</option>
            @foreach($statuses as $st)
                <option value="{{ $st->value }}" @selected(($filters['status'] ?? '') === $st->value)>{{ $st->label() }}</option>
            @endforeach
        </select>
        <select name="assigned_officer_id" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm">
            <option value="">All Assigned Officers</option>
            @foreach($officers as $off)
                <option value="{{ $off->id }}" @selected((string) ($filters['assigned_officer_id'] ?? '') === (string) $off->id)>{{ $off->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-md bg-slate-800 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-700">Filter</button>
    </form>

    @can('create', \App\Models\LicenseApplication::class)
    <a href="{{ route('licensing.applications.create') }}" class="rounded-md bg-emerald-700 px-4 py-1.5 text-sm font-semibold text-white hover:bg-emerald-800">
        + New Application
    </a>
    @endcan
</div>

<div class="rounded-lg bg-white border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">Application #</th>
                    <th class="px-4 py-3">Establishment &amp; LGA</th>
                    <th class="px-4 py-3">License Type</th>
                    <th class="px-4 py-3">Stage</th>
                    <th class="px-4 py-3">Assigned Officer</th>
                    <th class="px-4 py-3">Expiry</th>
                    <th class="px-4 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($applications as $app)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 font-mono text-xs font-semibold text-emerald-800">
                        <a href="{{ route('licensing.applications.show', $app) }}" class="hover:underline">{{ $app->application_number }}</a>
                        @if($app->license_number)<div class="text-slate-500">{{ $app->license_number }}</div>@endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="font-medium text-slate-900">{{ $app->hotel?->name }}</div>
                        <div class="text-xs text-slate-500">{{ $app->hotel?->lga?->name }} · {{ $app->application_type }}</div>
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-700">{{ $app->licenseType?->name }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $app->status->badgeColor() }}">{{ $app->status->label() }}</span>
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-700">{{ $app->assignedOfficer?->name ?? 'Unassigned' }}</td>
                    <td class="px-4 py-3 text-xs text-slate-600">{{ \App\Support\Format::date($app->expires_at) }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('licensing.applications.show', $app) }}" class="text-xs font-semibold text-emerald-700 hover:underline">Manage →</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">No license applications found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t border-slate-200">{{ $applications->links() }}</div>
</div>
@endsection
