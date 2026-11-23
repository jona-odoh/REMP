@extends('layouts.app')

@section('title', 'Cultural & Tourism Assets')
@section('heading', 'Cultural & Tourism Resource Registry')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3">
    <form method="GET" action="{{ route('culture.assets.index') }}" class="flex flex-wrap items-center gap-2">
        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search asset name..."
               aria-label="Search assets" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm">
        <select name="category_id" aria-label="Filter by category" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" @selected((string) ($filters['category_id'] ?? '') === (string) $cat->id)>{{ $cat->name }}</option>
            @endforeach
        </select>
        <select name="lga_id" aria-label="Filter by LGA" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm">
            <option value="">All 21 LGAs</option>
            @foreach($lgas as $lga)
                <option value="{{ $lga->id }}" @selected((string) ($filters['lga_id'] ?? '') === (string) $lga->id)>{{ $lga->name }}</option>
            @endforeach
        </select>
        <select name="condition" aria-label="Filter by condition" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm">
            <option value="">All Conditions</option>
            @foreach($conditions as $cond)
                <option value="{{ $cond->value }}" @selected(($filters['condition'] ?? '') === $cond->value)>{{ $cond->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-md bg-slate-800 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-700">Filter</button>
    </form>

    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('culture.assets.map', request()->query()) }}" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
            Map View
        </a>
        <a href="{{ route('culture.assets.export', array_merge(request()->query(), ['format' => 'xlsx'])) }}" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
            Export Excel
        </a>
        <a href="{{ route('culture.assets.export', array_merge(request()->query(), ['format' => 'csv'])) }}" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
            Export CSV
        </a>
        @can('create', \App\Models\CulturalAsset::class)
        <a href="{{ route('culture.assets.import.form') }}" class="rounded-md border border-emerald-700 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-800 hover:bg-emerald-100">
            Bulk Import
        </a>
        <a href="{{ route('culture.assets.create') }}" class="rounded-md bg-emerald-700 px-4 py-1.5 text-sm font-semibold text-white hover:bg-emerald-800">
            + Register Asset
        </a>
        @endcan
    </div>
</div>

<div class="rounded-lg bg-white border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">Asset Name</th>
                    <th class="px-4 py-3">Category &amp; LGA</th>
                    <th class="px-4 py-3">Ownership</th>
                    <th class="px-4 py-3">Condition</th>
                    <th class="px-4 py-3">Rev / Media</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($assets as $asset)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <a href="{{ route('culture.assets.show', $asset) }}" class="font-semibold text-emerald-800 hover:underline">{{ $asset->name }}</a>
                        <div class="text-xs text-slate-500 font-mono">
                            {{ $asset->location }} @if($asset->is_approximate_location) <span class="text-amber-700">(LGA centroid)</span> @endif
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="text-slate-800">{{ $asset->category?->name }}</div>
                        <div class="text-xs text-slate-500">{{ $asset->lga?->name }} ({{ $asset->lga?->senatorial_district }})</div>
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-700">{{ $asset->ownership_status?->label() }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $asset->condition_assessment->badgeColor() }}">
                            {{ $asset->condition_assessment->label() }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-600">
                        v{{ $asset->current_version }} · {{ $asset->documents_count }} file(s)
                    </td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <a href="{{ route('culture.assets.show', $asset) }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-900">View</a>
                        @can('update', $asset)
                        <a href="{{ route('culture.assets.edit', $asset) }}" class="text-xs font-medium text-slate-700 hover:text-slate-900">Edit</a>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-slate-500">No cultural or tourism assets found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t border-slate-200">{{ $assets->links() }}</div>
</div>
@endsection
