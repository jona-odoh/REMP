@extends('layouts.app')

@section('title', 'Ad-Hoc Query Builder')
@section('heading', 'Ad-Hoc Ministry Query Builder')

@section('content')
<div class="rounded-lg bg-white border border-slate-200 shadow-sm p-5 space-y-4">
    <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-3">
        @foreach(['hotels' => 'Hotels & Hospitality', 'assets' => 'Cultural & Tourism Assets', 'projects' => 'Projects & Programs'] as $key => $label)
            <a href="{{ route('reports.query-builder', ['module' => $key]) }}"
               class="rounded-md px-3 py-1.5 text-xs font-semibold {{ $module === $key ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('reports.query-builder') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-sm">
        <input type="hidden" name="module" value="{{ $module }}">
        <input type="hidden" name="run" value="1">

        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">LGA</label>
            <select name="lga_id" class="w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                <option value="">Any LGA</option>
                @foreach($lgas as $lga)
                    <option value="{{ $lga->id }}" @selected((string) request('lga_id') === (string) $lga->id)>{{ $lga->name }}</option>
                @endforeach
            </select>
        </div>

        @if($module === 'hotels')
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">License Status</label>
                <select name="license_status" class="w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                    <option value="">Any Status</option>
                    @foreach($hotelStatuses as $st)
                        <option value="{{ $st->value }}" @selected(request('license_status') === $st->value)>{{ $st->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Establishment Type</label>
                <select name="establishment_type" class="w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                    <option value="">Any Type</option>
                    @foreach($establishmentTypes as $type)
                        <option value="{{ $type }}" @selected(request('establishment_type') === $type)>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Minimum Rooms</label>
                <input name="min_rooms" type="number" min="0" value="{{ request('min_rooms') }}" placeholder="e.g. 20" class="w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm">
            </div>
        @elseif($module === 'assets')
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Category</label>
                <select name="category_id" class="w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                    <option value="">Any Category</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected((string) request('category_id') === (string) $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Condition</label>
                <select name="condition" class="w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                    <option value="">Any Condition</option>
                    @foreach($conditions as $cond)
                        <option value="{{ $cond->value }}" @selected(request('condition') === $cond->value)>{{ $cond->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Ownership</label>
                <select name="ownership" class="w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                    <option value="">Any Ownership</option>
                    @foreach($ownerships as $own)
                        <option value="{{ $own->value }}" @selected(request('ownership') === $own->value)>{{ $own->label() }}</option>
                    @endforeach
                </select>
            </div>
        @else
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Project Status</label>
                <select name="project_status" class="w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                    <option value="">Any Status</option>
                    @foreach($projectStatuses as $st)
                        <option value="{{ $st->value }}" @selected(request('project_status') === $st->value)>{{ $st->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-slate-600 mb-1">Funding Source Keyword</label>
                <input name="funding_source" type="text" value="{{ request('funding_source') }}" placeholder="e.g. Capital, Federal, UNESCO" class="w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm">
            </div>
        @endif

        <div class="sm:col-span-2 lg:col-span-4 flex items-center gap-2 pt-1">
            <button type="submit" class="rounded-md bg-emerald-700 px-4 py-1.5 text-sm font-semibold text-white hover:bg-emerald-800">Run Query</button>
            <a href="{{ route('reports.query-builder', ['module' => $module]) }}" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50">Reset</a>
        </div>
    </form>
</div>

@if($executed)
<div class="rounded-lg bg-white border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-5 py-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-slate-900">Query Results ({{ $results->count() }} record(s))</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            @if($module === 'hotels')
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                    <tr><th class="px-4 py-2.5">Hotel</th><th class="px-4 py-2.5">LGA</th><th class="px-4 py-2.5">Type</th><th class="px-4 py-2.5">Rooms</th><th class="px-4 py-2.5">License Status</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($results as $row)
                    <tr>
                        <td class="px-4 py-2.5 font-semibold"><a href="{{ route('licensing.hotels.show', $row) }}" class="text-emerald-800 hover:underline">{{ $row->name }}</a></td>
                        <td class="px-4 py-2.5">{{ $row->lga?->name }}</td>
                        <td class="px-4 py-2.5">{{ $row->establishment_type }}</td>
                        <td class="px-4 py-2.5">{{ $row->capacity_rooms }}</td>
                        <td class="px-4 py-2.5">{{ $row->license_status->label() }}</td>
                    </tr>
                    @empty <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No matching hotels found.</td></tr> @endforelse
                </tbody>
            @elseif($module === 'assets')
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                    <tr><th class="px-4 py-2.5">Asset Name</th><th class="px-4 py-2.5">Category</th><th class="px-4 py-2.5">LGA</th><th class="px-4 py-2.5">Ownership</th><th class="px-4 py-2.5">Condition</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($results as $row)
                    <tr>
                        <td class="px-4 py-2.5 font-semibold"><a href="{{ route('culture.assets.show', $row) }}" class="text-emerald-800 hover:underline">{{ $row->name }}</a></td>
                        <td class="px-4 py-2.5">{{ $row->category?->name }}</td>
                        <td class="px-4 py-2.5">{{ $row->lga?->name }}</td>
                        <td class="px-4 py-2.5">{{ $row->ownership_status?->label() }}</td>
                        <td class="px-4 py-2.5">{{ $row->condition_assessment?->label() }}</td>
                    </tr>
                    @empty <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No matching cultural assets found.</td></tr> @endforelse
                </tbody>
            @else
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                    <tr><th class="px-4 py-2.5">Project</th><th class="px-4 py-2.5">LGA</th><th class="px-4 py-2.5">Funding Source</th><th class="px-4 py-2.5">Status</th><th class="px-4 py-2.5">Utilization</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($results as $row)
                    <tr>
                        <td class="px-4 py-2.5 font-semibold"><a href="{{ route('projects.show', $row) }}" class="text-emerald-800 hover:underline">{{ $row->title }}</a></td>
                        <td class="px-4 py-2.5">{{ $row->lga?->name ?? 'Statewide' }}</td>
                        <td class="px-4 py-2.5">{{ $row->funding_source }}</td>
                        <td class="px-4 py-2.5">{{ $row->status->label() }}</td>
                        <td class="px-4 py-2.5">{{ $row->budgetUtilizationPercent() }}%</td>
                    </tr>
                    @empty <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No matching projects found.</td></tr> @endforelse
                </tbody>
            @endif
        </table>
    </div>
</div>
@endif
@endsection
