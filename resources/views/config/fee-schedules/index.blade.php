@extends('layouts.app')

@section('title', 'Fee Schedules')
@section('heading', 'System Configuration — License Fee Schedules (NGN)')

@section('content')
@can('create', \App\Models\FeeSchedule::class)
<div class="rounded-lg bg-white border border-slate-200 shadow-sm p-5">
    <h3 class="text-sm font-semibold text-slate-900 mb-3">Add Fee Schedule</h3>
    <form method="POST" action="{{ route('config.fee-schedules.store') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-3">
        @csrf
        <select name="license_type_id" required class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
            @foreach($licenseTypes as $type)
                <option value="{{ $type->id }}">{{ $type->name }}</option>
            @endforeach
        </select>
        <input name="classification_rating" type="text" placeholder="Classification (e.g. 3-Star Hotel) *" required class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <input name="amount" type="number" step="0.01" min="0" placeholder="Amount (₦) *" required class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <input name="effective_from" type="date" value="{{ now()->toDateString() }}" required class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <input type="hidden" name="is_active" value="1">
        <button type="submit" class="rounded-md bg-emerald-700 px-3 py-1.5 text-sm font-semibold text-white hover:bg-emerald-800">Add Fee</button>
    </form>
</div>
@endcan

<div class="rounded-lg bg-white border border-slate-200 shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
            <tr>
                <th class="px-4 py-3">License Type</th>
                <th class="px-4 py-3">Classification / Rating</th>
                <th class="px-4 py-3">Fee Amount</th>
                <th class="px-4 py-3">Effective From</th>
                <th class="px-4 py-3">Status</th>
                @can('delete', \App\Models\FeeSchedule::class)<th class="px-4 py-3 text-right">Actions</th>@endcan
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($feeSchedules as $fee)
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-2.5 text-slate-700">{{ $fee->licenseType?->name ?? '—' }}</td>
                <td class="px-4 py-2.5 font-semibold text-slate-900">{{ $fee->classification_rating }}</td>
                <td class="px-4 py-2.5 font-semibold text-emerald-800">{{ \App\Support\Format::naira($fee->amount) }}</td>
                <td class="px-4 py-2.5 text-slate-600">{{ \App\Support\Format::date($fee->effective_from) }}</td>
                <td class="px-4 py-2.5">
                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $fee->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
                        {{ $fee->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                @can('delete', $fee)
                <td class="px-4 py-2.5 text-right">
                    <form method="POST" action="{{ route('config.fee-schedules.destroy', $fee) }}" onsubmit="return confirm('Delete this fee schedule?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs font-medium text-rose-700 hover:text-rose-900">Delete</button>
                    </form>
                </td>
                @endcan
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="px-4 py-3 border-t border-slate-200">{{ $feeSchedules->links() }}</div>
</div>
@endsection
