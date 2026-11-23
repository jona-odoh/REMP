@extends('layouts.app')

@section('title', $report['title'])
@section('heading', $report['title'])

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3">
    <a href="{{ route('reports.standard') }}" class="text-sm font-medium text-emerald-700 hover:text-emerald-900">
        ← Back to Standard Reports
    </a>

    <div class="flex flex-wrap items-center gap-2">
        <button type="button" onclick="window.print()"
                class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
            Print
        </button>
        @if(auth()->user()->can('reports.export'))
            <a href="{{ route('reports.standard.download', [$reportKey, 'pdf']) }}"
               class="rounded-md bg-rose-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-800">
                Download PDF
            </a>
            <a href="{{ route('reports.standard.download', [$reportKey, 'xlsx']) }}"
               class="rounded-md bg-emerald-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-800">
                Download Excel (.xlsx)
            </a>
        @endif
    </div>
</div>

<div class="rounded-lg bg-white border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-200 bg-slate-50 flex flex-wrap items-center justify-between gap-2">
        <div>
            <h3 class="text-base font-bold text-slate-900">{{ $report['title'] }}</h3>
            <p class="text-xs text-slate-600 mt-0.5">{{ $report['subtitle'] }}</p>
        </div>
        <div class="text-right text-xs text-slate-500">
            <div>Records: <strong class="text-slate-900">{{ count($report['rows']) }}</strong></div>
            <div>Generated: {{ $report['generated_at'] }} (WAT)</div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                <tr>
                    @foreach($report['headings'] as $heading)
                        <th class="px-4 py-3 whitespace-nowrap">{{ $heading }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($report['rows'] as $row)
                <tr class="hover:bg-slate-50">
                    @foreach($row as $cell)
                        <td class="px-4 py-2.5 text-slate-800">{{ $cell }}</td>
                    @endforeach
                </tr>
                @empty
                <tr>
                    <td colspan="{{ count($report['headings']) }}" class="px-4 py-8 text-center text-slate-500">
                        No records available for this report.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
