@extends('layouts.app')

@section('title', 'Intangible Heritage')
@section('heading', 'Intangible Cultural Heritage & Festivals Registry')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3">
    <form method="GET" action="{{ route('culture.heritage.index') }}" class="flex flex-wrap items-center gap-2">
        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search festival, community..."
               aria-label="Search intangible heritage" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm">
        <select name="lga_id" aria-label="Filter by LGA" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm">
            <option value="">All 21 LGAs</option>
            @foreach($lgas as $lga)
                <option value="{{ $lga->id }}" @selected((string) ($filters['lga_id'] ?? '') === (string) $lga->id)>{{ $lga->name }}</option>
            @endforeach
        </select>
        <select name="status" aria-label="Filter by safeguarding status" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm">
            <option value="">All Safeguarding Statuses</option>
            @foreach($statuses as $status)
                <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-md bg-slate-800 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-700">Filter</button>
    </form>

    @can('create', \App\Models\IntangibleHeritage::class)
    <a href="{{ route('culture.heritage.create') }}" class="rounded-md bg-emerald-700 px-4 py-1.5 text-sm font-semibold text-white hover:bg-emerald-800">
        + Document Festival / Tradition
    </a>
    @endcan
</div>

<div class="rounded-lg bg-white border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">Festival / Tradition</th>
                    <th class="px-4 py-3">LGA &amp; Communities</th>
                    <th class="px-4 py-3">Festival Season</th>
                    <th class="px-4 py-3">Safeguarding Status</th>
                    <th class="px-4 py-3">Media</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($heritages as $item)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <a href="{{ route('culture.heritage.show', $item) }}" class="font-semibold text-emerald-800 hover:underline">{{ $item->name }}</a>
                    </td>
                    <td class="px-4 py-3">
                        <div class="text-slate-800">{{ $item->lga?->name ?? 'Statewide' }}</div>
                        <div class="text-xs text-slate-500 truncate max-w-xs">{{ $item->participating_communities }}</div>
                    </td>
                    <td class="px-4 py-3 text-slate-700">{{ $item->festival_season }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $item->safeguarding_status->badgeColor() }}">
                            {{ $item->safeguarding_status->label() }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-600">{{ $item->documents_count }} file(s)</td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <a href="{{ route('culture.heritage.show', $item) }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-900">View</a>
                        @can('update', $item)
                        <a href="{{ route('culture.heritage.edit', $item) }}" class="text-xs font-medium text-slate-700 hover:text-slate-900">Edit</a>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-slate-500">No intangible heritage records found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t border-slate-200">{{ $heritages->links() }}</div>
</div>
@endsection
