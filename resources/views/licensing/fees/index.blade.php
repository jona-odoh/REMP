@extends('layouts.app')

@section('title', 'License Fees & Revenue')
@section('heading', 'Hospitality Licensing Fees & Revenue Tracker (NGN)')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-5">
        <p class="text-xs font-semibold uppercase text-emerald-700">Total Collected Revenue</p>
        <p class="mt-2 text-2xl font-bold text-emerald-900">{{ \App\Support\Format::naira($totalRevenue) }}</p>
    </div>
    <div class="rounded-lg bg-white border border-slate-200 p-5">
        <p class="text-xs font-semibold uppercase text-slate-500 mb-2">Revenue by Month</p>
        <div class="space-y-1 text-xs">
            @forelse($revenueByMonth->take(4) as $m => $sum)
                <div class="flex justify-between"><span>{{ $m }}</span><span class="font-semibold">{{ \App\Support\Format::naira($sum) }}</span></div>
            @empty <p class="text-slate-400">No payments yet.</p> @endforelse
        </div>
    </div>
    <div class="rounded-lg bg-white border border-slate-200 p-5">
        <p class="text-xs font-semibold uppercase text-slate-500 mb-2">Revenue by LGA</p>
        <div class="space-y-1 text-xs">
            @forelse($revenueByLga->take(4) as $lgaName => $sum)
                <div class="flex justify-between"><span>{{ $lgaName }}</span><span class="font-semibold">{{ \App\Support\Format::naira($sum) }}</span></div>
            @empty <p class="text-slate-400">No payments yet.</p> @endforelse
        </div>
    </div>
</div>

@can('create', \App\Models\LicenseFee::class)
<div class="rounded-lg bg-white border border-slate-200 shadow-sm p-5">
    <h3 class="text-sm font-semibold text-slate-900 mb-3">Record Fee Payment Receipt</h3>
    <form method="POST" action="{{ route('licensing.fees.store') }}" class="grid grid-cols-1 sm:grid-cols-6 gap-3">
        @csrf
        <select name="hotel_id" required class="sm:col-span-2 rounded-md border border-slate-300 px-3 py-1.5 text-sm">
            <option value="">Select Hotel *</option>
            @foreach($hotels as $h)
                <option value="{{ $h->id }}">{{ $h->name }} ({{ $h->lga?->name }})</option>
            @endforeach
        </select>
        <input name="amount" type="number" step="0.01" min="1" placeholder="Amount (₦) *" required class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <input name="receipt_reference" type="text" placeholder="Receipt Ref *" required class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <input name="paid_at" type="date" value="{{ now()->toDateString() }}" required class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <input name="period" type="text" value="{{ now()->year }} Annual" required class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <div class="sm:col-span-6 flex justify-end">
            <button type="submit" class="rounded-md bg-emerald-700 px-4 py-1.5 text-sm font-semibold text-white hover:bg-emerald-800">Record Payment</button>
        </div>
    </form>
</div>
@endcan

<div class="flex flex-wrap items-center justify-between gap-2">
    <form method="GET" action="{{ route('licensing.fees.index') }}" class="flex flex-wrap items-center gap-2">
        <select name="lga_id" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm">
            <option value="">All 21 LGAs</option>
            @foreach($lgas as $lga)
                <option value="{{ $lga->id }}" @selected((int) ($filters['lga_id'] ?? 0) === $lga->id)>{{ $lga->name }}</option>
            @endforeach
        </select>
        <input name="month" type="month" value="{{ $filters['month'] ?? '' }}" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm">
        <button type="submit" class="rounded-md bg-slate-800 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-700">Filter</button>
    </form>

    <a href="{{ route('licensing.fees.export', request()->query()) }}" class="rounded-md border border-emerald-700 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-800 hover:bg-emerald-100">
        Export Revenue to Excel (.xlsx)
    </a>
</div>

<div class="rounded-lg bg-white border border-slate-200 shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
            <tr>
                <th class="px-4 py-3">Receipt Ref</th>
                <th class="px-4 py-3">Hotel &amp; LGA</th>
                <th class="px-4 py-3">Period</th>
                <th class="px-4 py-3">Paid Date</th>
                <th class="px-4 py-3 text-right">Amount</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($fees as $fee)
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-2.5 font-mono text-xs text-slate-700">{{ $fee->receipt_reference }}</td>
                <td class="px-4 py-2.5">
                    <div class="font-medium text-slate-900">{{ $fee->hotel?->name }}</div>
                    <div class="text-xs text-slate-500">{{ $fee->hotel?->lga?->name }}</div>
                </td>
                <td class="px-4 py-2.5 text-slate-700">{{ $fee->period }}</td>
                <td class="px-4 py-2.5 text-slate-600">{{ \App\Support\Format::date($fee->paid_at) }}</td>
                <td class="px-4 py-2.5 text-right font-semibold text-emerald-800">{{ \App\Support\Format::naira($fee->amount) }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">No license fee payments recorded.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-4 py-3 border-t border-slate-200">{{ $fees->links() }}</div>
</div>
@endsection
