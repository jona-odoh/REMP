@extends('layouts.app')

@section('title', 'Hotel & Hospitality Registry')
@section('heading', 'Hotel & Hospitality Establishment Registry')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3">
    <form method="GET" action="{{ route('licensing.hotels.index') }}" class="flex flex-wrap items-center gap-2">
        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search hotel, owner..."
               aria-label="Search hotels" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm">
        <select name="lga_id" aria-label="Filter by LGA" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm">
            <option value="">All 21 LGAs</option>
            @foreach($lgas as $lga)
                <option value="{{ $lga->id }}" @selected((string) ($filters['lga_id'] ?? '') === (string) $lga->id)>{{ $lga->name }}</option>
            @endforeach
        </select>
        <select name="license_status" aria-label="Filter by status" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm">
            <option value="">All License Statuses</option>
            @foreach($statuses as $st)
                <option value="{{ $st->value }}" @selected(($filters['license_status'] ?? '') === $st->value)>{{ $st->label() }}</option>
            @endforeach
        </select>
        <select name="establishment_type" aria-label="Filter by type" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm">
            <option value="">All Types</option>
            @foreach($establishmentTypes as $type)
                <option value="{{ $type }}" @selected(($filters['establishment_type'] ?? '') === $type)>{{ $type }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-md bg-slate-800 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-700">Filter</button>
    </form>

    @can('create', \App\Models\Hotel::class)
    <a href="{{ route('licensing.hotels.create') }}" class="rounded-md bg-emerald-700 px-4 py-1.5 text-sm font-semibold text-white hover:bg-emerald-800">
        + Register Establishment
    </a>
    @endcan
</div>

<div class="rounded-lg bg-white border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">Establishment</th>
                    <th class="px-4 py-3">LGA &amp; Type</th>
                    <th class="px-4 py-3">Capacity</th>
                    <th class="px-4 py-3">Classification</th>
                    <th class="px-4 py-3">License Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($hotels as $hotel)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <a href="{{ route('licensing.hotels.show', $hotel) }}" class="font-semibold text-emerald-800 hover:underline">{{ $hotel->name }}</a>
                        <div class="text-xs text-slate-500">{{ $hotel->owner_name }} · {{ $hotel->owner_contact }}</div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="text-slate-800">{{ $hotel->lga?->name }}</div>
                        <div class="text-xs text-slate-500">{{ $hotel->establishment_type }}</div>
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-700">{{ $hotel->capacity_rooms }} rooms / {{ $hotel->capacity_beds }} beds</td>
                    <td class="px-4 py-3 text-xs text-slate-700">{{ $hotel->classification_rating }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $hotel->license_status->badgeColor() }}">
                            {{ $hotel->license_status->label() }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <a href="{{ route('licensing.hotels.show', $hotel) }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-900">View</a>
                        @can('update', $hotel)
                        <a href="{{ route('licensing.hotels.edit', $hotel) }}" class="text-xs font-medium text-slate-700 hover:text-slate-900">Edit</a>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-slate-500">No hospitality establishments found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t border-slate-200">{{ $hotels->links() }}</div>
</div>
@endsection
