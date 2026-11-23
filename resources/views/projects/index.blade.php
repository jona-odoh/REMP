@extends('layouts.app')

@section('title', 'Projects & Programs')
@section('heading', 'Ministry Projects & Program Tracking')

@section('content')
@if($flaggedProjects->isNotEmpty())
<div class="rounded-lg bg-rose-50 border border-rose-300 p-4 shadow-sm">
    <div class="flex items-center justify-between">
        <h3 class="text-sm font-semibold text-rose-900">Budget Variance Alert (&gt; {{ $threshold }}% threshold)</h3>
        <span class="text-xs font-semibold text-rose-800">{{ $flaggedProjects->count() }} project(s) flagged</span>
    </div>
    <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
        @foreach($flaggedProjects as $fp)
        <a href="{{ route('projects.show', $fp) }}" class="flex items-center justify-between rounded bg-white border border-rose-200 px-3 py-2 hover:bg-rose-50">
            <span class="font-semibold text-slate-900">{{ $fp->title }}</span>
            <span class="font-mono font-bold text-rose-700">{{ $fp->variancePercent() > 0 ? '+' : '' }}{{ $fp->variancePercent() }}%</span>
        </a>
        @endforeach
    </div>
</div>
@endif

<div class="flex flex-wrap items-center justify-between gap-3">
    <form method="GET" action="{{ route('projects.index') }}" class="flex flex-wrap items-center gap-2">
        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search title, budget line..." class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm">
        <select name="lga_id" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm">
            <option value="">All 21 LGAs</option>
            @foreach($lgas as $lga)
                <option value="{{ $lga->id }}" @selected((string) ($filters['lga_id'] ?? '') === (string) $lga->id)>{{ $lga->name }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm">
            <option value="">All Statuses</option>
            @foreach($statuses as $st)
                <option value="{{ $st->value }}" @selected(($filters['status'] ?? '') === $st->value)>{{ $st->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-md bg-slate-800 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-700">Filter</button>
    </form>

    @can('create', \App\Models\Project::class)
    <a href="{{ route('projects.create') }}" class="rounded-md bg-emerald-700 px-4 py-1.5 text-sm font-semibold text-white hover:bg-emerald-800">
        + Register Project
    </a>
    @endcan
</div>

<div class="rounded-lg bg-white border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">Project &amp; Budget Line</th>
                    <th class="px-4 py-3">LGA / Timeline</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Budget vs. Actual</th>
                    <th class="px-4 py-3">Milestones</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($projects as $project)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <a href="{{ route('projects.show', $project) }}" class="font-semibold text-emerald-800 hover:underline">{{ $project->title }}</a>
                        <div class="text-xs text-slate-500 font-mono">{{ $project->budget_line }} · {{ $project->funding_source }}</div>
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-700">
                        <div>{{ $project->lga?->name ?? 'Statewide' }}</div>
                        <div class="text-slate-500">{{ \App\Support\Format::date($project->start_date) }} → {{ \App\Support\Format::date($project->end_date) }}</div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $project->status->badgeColor() }}">{{ $project->status->label() }}</span>
                    </td>
                    <td class="px-4 py-3 text-xs">
                        <div class="font-semibold text-slate-900">{{ \App\Support\Format::naira($project->totalActualExpenditure()) }} / {{ \App\Support\Format::naira($project->totalApprovedBudget()) }}</div>
                        <div class="{{ $project->hasVarianceAlert($threshold) ? 'text-rose-700 font-semibold' : 'text-slate-500' }}">
                            Utilization: {{ $project->budgetUtilizationPercent() }}%
                            @if($project->hasVarianceAlert($threshold)) · VARIANCE FLAGGED @endif
                        </div>
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-700">
                        {{ $project->milestones->whereNotNull('completed_at')->count() }} / {{ $project->milestones->count() }} done
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('projects.show', $project) }}" class="text-xs font-semibold text-emerald-700 hover:underline">Manage →</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">No projects registered.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t border-slate-200">{{ $projects->links() }}</div>
</div>
@endsection
