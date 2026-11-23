@extends('layouts.app')

@section('title', 'Hospitality Compliance Dashboard')
@section('heading', 'Hotel & Hospitality Compliance Dashboard')

@section('content')
<form method="GET" action="{{ route('licensing.compliance.index') }}" class="flex flex-wrap items-center gap-2">
    <select name="lga_id" aria-label="Filter by LGA" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm">
        <option value="">All 21 LGAs</option>
        @foreach($lgas as $lga)
            <option value="{{ $lga->id }}" @selected((string) ($filters['lga_id'] ?? '') === (string) $lga->id)>{{ $lga->name }}</option>
        @endforeach
    </select>
    <select name="establishment_type" aria-label="Filter by establishment type" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm">
        <option value="">All Establishment Types</option>
        @foreach($establishmentTypes as $type)
            <option value="{{ $type }}" @selected(($filters['establishment_type'] ?? '') === $type)>{{ $type }}</option>
        @endforeach
    </select>
    <button type="submit" class="rounded-md bg-slate-800 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-700">Filter</button>
    <a href="{{ route('licensing.compliance.index') }}" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50">Reset</a>
</form>

<div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
    <div class="rounded-lg bg-white p-4 border border-slate-200 shadow-sm">
        <p class="text-xs font-semibold uppercase text-slate-500">Total Registered</p>
        <p class="mt-2 text-2xl font-bold text-slate-900">{{ $counts['total'] }}</p>
    </div>
    <div class="rounded-lg bg-emerald-50 p-4 border border-emerald-200 shadow-sm">
        <p class="text-xs font-semibold uppercase text-emerald-700">Licensed</p>
        <p class="mt-2 text-2xl font-bold text-emerald-900">{{ $counts['licensed'] }}</p>
    </div>
    <div class="rounded-lg bg-amber-50 p-4 border border-amber-200 shadow-sm">
        <p class="text-xs font-semibold uppercase text-amber-700">Pending</p>
        <p class="mt-2 text-2xl font-bold text-amber-900">{{ $counts['pending'] }}</p>
    </div>
    <div class="rounded-lg bg-rose-50 p-4 border border-rose-200 shadow-sm">
        <p class="text-xs font-semibold uppercase text-rose-700">Expired</p>
        <p class="mt-2 text-2xl font-bold text-rose-900">{{ $counts['expired'] }}</p>
    </div>
    <div class="rounded-lg bg-slate-100 p-4 border border-slate-300 shadow-sm">
        <p class="text-xs font-semibold uppercase text-slate-600">Unlicensed</p>
        <p class="mt-2 text-2xl font-bold text-slate-900">{{ $counts['unlicensed'] }}</p>
    </div>
</div>

<div class="rounded-lg bg-white border border-rose-300 shadow-sm overflow-hidden">
    <div class="px-5 py-3 bg-rose-50 border-b border-rose-200 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-rose-900">Overdue Inspections ({{ $overdueInspections->count() }})</h3>
        <a href="{{ route('licensing.inspections.index', ['overdue' => 1]) }}" class="text-xs font-semibold text-rose-800 hover:underline">View All Inspections →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-2.5">Hotel</th>
                    <th class="px-4 py-2.5">LGA</th>
                    <th class="px-4 py-2.5">Assigned Officer</th>
                    <th class="px-4 py-2.5">Scheduled Date</th>
                    <th class="px-4 py-2.5 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($overdueInspections as $insp)
                <tr class="bg-rose-50/40">
                    <td class="px-4 py-2.5 font-semibold text-slate-900">{{ $insp->hotel?->name }}</td>
                    <td class="px-4 py-2.5 text-slate-700">{{ $insp->hotel?->lga?->name }}</td>
                    <td class="px-4 py-2.5 text-slate-700">{{ $insp->assignedOfficer?->name }}</td>
                    <td class="px-4 py-2.5 font-semibold text-rose-700">{{ \App\Support\Format::date($insp->scheduled_date) }} (Overdue)</td>
                    <td class="px-4 py-2.5 text-right">
                        <a href="{{ route('licensing.inspections.show', $insp) }}" class="text-xs font-semibold text-emerald-700 hover:underline">Conduct Inspection →</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center text-xs text-slate-500">No overdue inspections.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
