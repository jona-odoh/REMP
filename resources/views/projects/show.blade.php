@extends('layouts.app')

@section('title', $project->title)
@section('heading', $project->title)

@section('content')
<div class="flex flex-wrap items-center justify-between gap-2">
    <a href="{{ route('projects.index') }}" class="text-sm font-medium text-emerald-700 hover:text-emerald-900">← Back to Projects</a>
    <div class="flex items-center gap-2">
        @can('update', $project)
        <a href="{{ route('projects.edit', $project) }}" class="rounded-md bg-emerald-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-800">Edit Project</a>
        @endcan
        @can('delete', $project)
        <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('Delete this project?')">
            @csrf @method('DELETE')
            <button type="submit" class="rounded-md border border-rose-300 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-800 hover:bg-rose-100">Delete</button>
        </form>
        @endcan
    </div>
</div>

<div class="rounded-lg bg-white border border-slate-200 shadow-sm p-6 space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $project->status->badgeColor() }}">{{ $project->status->label() }}</span>
        @if($project->hasVarianceAlert($threshold))
            <span class="inline-flex rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-800">
                Budget Variance Flagged ({{ $project->variancePercent() > 0 ? '+' : '' }}{{ $project->variancePercent() }}% vs ±{{ $threshold }}% limit)
            </span>
        @endif
    </div>

    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
        <div><dt class="text-xs uppercase text-slate-500">Budget Line</dt><dd class="mt-1 font-mono text-xs font-semibold">{{ $project->budget_line }}</dd></div>
        <div><dt class="text-xs uppercase text-slate-500">Funding Source</dt><dd class="mt-1 font-medium">{{ $project->funding_source }}</dd></div>
        <div><dt class="text-xs uppercase text-slate-500">Implementing Partner</dt><dd class="mt-1 font-medium">{{ $project->implementing_partner ?? 'In-House' }}</dd></div>
        <div><dt class="text-xs uppercase text-slate-500">Target LGA</dt><dd class="mt-1 font-medium">{{ $project->lga?->name ?? 'Statewide' }}</dd></div>
        <div><dt class="text-xs uppercase text-slate-500">Timeline</dt><dd class="mt-1 font-medium">{{ \App\Support\Format::date($project->start_date) }} → {{ \App\Support\Format::date($project->end_date) }}</dd></div>
        <div><dt class="text-xs uppercase text-slate-500">Budget Utilization</dt><dd class="mt-1 font-bold text-emerald-800">{{ $project->budgetUtilizationPercent() }}%</dd></div>
    </dl>

    @if($project->description)
    <p class="pt-3 border-t border-slate-200 text-sm text-slate-700">{{ $project->description }}</p>
    @endif
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Milestones -->
    <div class="rounded-lg bg-white border border-slate-200 shadow-sm p-5 space-y-4">
        <h3 class="text-sm font-semibold text-slate-900">Milestones ({{ $project->milestones->count() }})</h3>

        @can('update', $project)
        <form method="POST" action="{{ route('projects.milestones.store', $project) }}" class="grid grid-cols-1 sm:grid-cols-3 gap-2 bg-slate-50 p-3 rounded border border-slate-200">
            @csrf
            <input name="title" type="text" placeholder="Milestone title *" required class="sm:col-span-3 rounded border border-slate-300 bg-white px-2.5 py-1.5 text-xs">
            <input name="due_date" type="date" required class="rounded border border-slate-300 bg-white px-2.5 py-1.5 text-xs">
            <select name="responsible_officer_id" class="rounded border border-slate-300 bg-white px-2.5 py-1.5 text-xs">
                <option value="">Assign Officer</option>
                @foreach($officers as $off)
                    <option value="{{ $off->id }}">{{ $off->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded bg-emerald-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-800">+ Add</button>
        </form>
        @endcan

        <div class="space-y-2 text-xs">
            @forelse($project->milestones as $ms)
            <div class="flex items-center justify-between rounded-md border {{ $ms->isOverdue() ? 'border-rose-300 bg-rose-50/50' : 'border-slate-200' }} p-3">
                <div>
                    <div class="font-semibold {{ $ms->isCompleted() ? 'line-through text-slate-500' : 'text-slate-900' }}">{{ $ms->title }}</div>
                    <div class="text-slate-500">Due: {{ \App\Support\Format::date($ms->due_date) }} · {{ $ms->responsibleOfficer?->name ?? 'Unassigned' }}</div>
                </div>
                @can('update', $project)
                <form method="POST" action="{{ route('projects.milestones.toggle', [$project, $ms]) }}">
                    @csrf
                    <button type="submit" class="rounded border px-2 py-1 text-[11px] font-semibold {{ $ms->isCompleted() ? 'border-slate-300 text-slate-600' : 'border-emerald-300 bg-emerald-50 text-emerald-800' }}">
                        {{ $ms->isCompleted() ? 'Reopen' : 'Mark Done' }}
                    </button>
                </form>
                @endcan
            </div>
            @empty
            <p class="text-slate-500">No milestones recorded yet.</p>
            @endforelse
        </div>
    </div>

    <!-- Budget vs Actual -->
    <div class="rounded-lg bg-white border border-slate-200 shadow-sm p-5 space-y-4">
        <h3 class="text-sm font-semibold text-slate-900">Budget vs. Actual Expenditure (NGN)</h3>

        @can('update', $project)
        <form method="POST" action="{{ route('projects.expenditures.store', $project) }}" class="grid grid-cols-1 sm:grid-cols-4 gap-2 bg-slate-50 p-3 rounded border border-slate-200">
            @csrf
            <input name="fiscal_year" type="text" value="{{ now()->year }}" required class="rounded border border-slate-300 bg-white px-2.5 py-1.5 text-xs">
            <input name="approved_budget" type="number" step="0.01" min="0" placeholder="Approved (₦) *" required class="rounded border border-slate-300 bg-white px-2.5 py-1.5 text-xs">
            <input name="actual_expenditure" type="number" step="0.01" min="0" placeholder="Actual (₦) *" required class="rounded border border-slate-300 bg-white px-2.5 py-1.5 text-xs">
            <button type="submit" class="rounded bg-emerald-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-800">Record</button>
        </form>
        @endcan

        <div class="space-y-2 text-xs">
            @forelse($project->expenditures as $exp)
            <div class="rounded-md border {{ $exp->variance_flag ? 'border-rose-300 bg-rose-50/50' : 'border-slate-200' }} p-3">
                <div class="flex items-center justify-between font-semibold">
                    <span>FY {{ $exp->fiscal_year }}</span>
                    <span class="{{ $exp->variance_flag ? 'text-rose-700' : 'text-emerald-800' }}">
                        Variance: {{ $exp->variance_percent > 0 ? '+' : '' }}{{ $exp->variance_percent }}%
                        @if($exp->variance_flag) (FLAGGED) @endif
                    </span>
                </div>
                <div class="mt-1 text-slate-700">
                    Approved: {{ \App\Support\Format::naira($exp->approved_budget) }} · Actual: {{ \App\Support\Format::naira($exp->actual_expenditure) }}
                </div>
            </div>
            @empty
            <p class="text-slate-500">No budget records yet.</p>
            @endforelse
        </div>
    </div>
</div>

<x-documents-panel :documents="$project->documents" documentable-type="project" :documentable-id="$project->id" :categories="['Project Proposal', 'Contract / MOU', 'Progress Report', 'Site Photo', 'Valuation Certificate']" />
@endsection
