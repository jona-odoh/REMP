@extends('layouts.app')

@section('title', 'Executive KPI Dashboard')
@section('heading', 'Director & Commissioner Decision-Support Dashboard')

@section('content')
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="rounded-lg bg-white border border-slate-200 p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase text-slate-500">Documented Heritage Assets</p>
        <p class="mt-2 text-2xl font-bold text-emerald-700">{{ $kpis['heritage_assets_count'] }}</p>
        <p class="text-xs text-slate-500 mt-1">+ {{ $kpis['intangible_heritage_count'] }} intangible heritage festivals</p>
    </div>

    <div class="rounded-lg bg-white border border-slate-200 p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase text-slate-500">Licensed Hotels</p>
        <p class="mt-2 text-2xl font-bold text-slate-900">{{ $kpis['licensed_hotels'] }} / {{ $kpis['total_hotels'] }}</p>
        <p class="text-xs text-emerald-700 font-semibold mt-1">{{ $kpis['licensed_percentage'] }}% compliance rate</p>
    </div>

    <div class="rounded-lg bg-white border border-slate-200 p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase text-slate-500">Active Projects &amp; Budget Utilization</p>
        <p class="mt-2 text-2xl font-bold text-slate-900">{{ $kpis['active_projects'] }} <span class="text-sm font-normal text-slate-500">/ {{ $kpis['total_projects'] }} total</span></p>
        <p class="text-xs text-slate-600 mt-1">{{ $kpis['budget_utilization_pct'] }}% of {{ \App\Support\Format::naira($kpis['total_approved_budget']) }}</p>
    </div>

    <div class="rounded-lg bg-white border border-slate-200 p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase text-slate-500">Inspections Completed (This Month)</p>
        <p class="mt-2 text-2xl font-bold text-slate-900">{{ $kpis['inspections_completed_month'] }}</p>
        <p class="text-xs {{ $kpis['overdue_inspections'] > 0 ? 'text-rose-700 font-semibold' : 'text-slate-500' }} mt-1">
            {{ $kpis['overdue_inspections'] }} overdue inspection(s)
        </p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="rounded-lg bg-white border border-slate-200 p-5 shadow-sm">
        <h3 class="text-sm font-semibold text-slate-900 mb-3">6-Month Licensing Revenue Trend (₦ NGN)</h3>
        <canvas id="revenueTrendChart" height="160"></canvas>
    </div>

    <div class="rounded-lg bg-white border border-slate-200 p-5 shadow-sm">
        <h3 class="text-sm font-semibold text-slate-900 mb-3">Assets &amp; Hotels by Local Government Area</h3>
        <canvas id="lgaBreakdownChart" height="160"></canvas>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    new window.Chart(document.getElementById('revenueTrendChart'), {
        type: 'line',
        data: {
            labels: @json($kpis['revenue_trend_labels']),
            datasets: [{
                label: 'License Revenue (₦)',
                data: @json($kpis['revenue_trend_values']),
                borderColor: '#047857',
                backgroundColor: 'rgba(4, 120, 87, 0.12)',
                fill: true,
                tension: 0.3
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });

    new window.Chart(document.getElementById('lgaBreakdownChart'), {
        type: 'bar',
        data: {
            labels: @json($kpis['lga_labels']),
            datasets: [
                { label: 'Cultural Assets', data: @json($kpis['lga_assets']), backgroundColor: '#047857' },
                { label: 'Hotels', data: @json($kpis['lga_hotels']), backgroundColor: '#0284c7' }
            ]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });
});
</script>
@endsection
