@extends('layouts.app')

@section('title', 'Hotel Inspections')
@section('heading', 'Hospitality Inspection Schedule & Reports')

@section('content')
@can('create', \App\Models\Inspection::class)
<div class="rounded-lg bg-white border border-slate-200 shadow-sm p-5">
    <h3 class="text-sm font-semibold text-slate-900 mb-3">Schedule New Inspection</h3>
    <form method="POST" action="{{ route('licensing.inspections.store') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
        @csrf
        <select name="hotel_id" required class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
            <option value="">Select Hotel *</option>
            @foreach($hotels as $h)
                <option value="{{ $h->id }}">{{ $h->name }}</option>
            @endforeach
        </select>
        <select name="assigned_officer_id" required class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
            @foreach($officers as $off)
                <option value="{{ $off->id }}">{{ $off->name }}</option>
            @endforeach
        </select>
        <input name="scheduled_date" type="date" value="{{ now()->addDays(2)->toDateString() }}" required class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <button type="submit" class="rounded-md bg-emerald-700 px-3 py-1.5 text-sm font-semibold text-white hover:bg-emerald-800">Schedule</button>
    </form>
</div>
@endcan

<div class="rounded-lg bg-white border border-slate-200 shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
            <tr>
                <th class="px-4 py-3">Hotel &amp; LGA</th>
                <th class="px-4 py-3">Assigned Officer</th>
                <th class="px-4 py-3">Scheduled Date</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Result</th>
                <th class="px-4 py-3 text-right">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($inspections as $insp)
            <tr class="{{ $insp->isOverdue() ? 'bg-rose-50/60' : 'hover:bg-slate-50' }}">
                <td class="px-4 py-3">
                    <div class="font-semibold text-slate-900">{{ $insp->hotel?->name }}</div>
                    <div class="text-xs text-slate-500">{{ $insp->hotel?->lga?->name }}</div>
                </td>
                <td class="px-4 py-3 text-slate-700">{{ $insp->assignedOfficer?->name }}</td>
                <td class="px-4 py-3">
                    <span class="{{ $insp->isOverdue() ? 'font-semibold text-rose-700' : 'text-slate-700' }}">
                        {{ \App\Support\Format::date($insp->scheduled_date) }}
                        @if($insp->isOverdue()) (OVERDUE) @endif
                    </span>
                </td>
                <td class="px-4 py-3">
                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $insp->status->badgeColor() }}">{{ $insp->status->label() }}</span>
                </td>
                <td class="px-4 py-3 text-xs">
                    @if($insp->overall_passed === true) <span class="font-semibold text-emerald-700">Passed</span>
                    @elseif($insp->overall_passed === false) <span class="font-semibold text-rose-700">Failed</span>
                    @else <span class="text-slate-400">Pending</span> @endif
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('licensing.inspections.show', $insp) }}" class="text-xs font-semibold text-emerald-700 hover:underline">Open →</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">No inspections recorded.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-4 py-3 border-t border-slate-200">{{ $inspections->links() }}</div>
</div>
@endsection
