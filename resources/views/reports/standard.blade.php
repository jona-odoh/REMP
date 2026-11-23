@extends('layouts.app')

@section('title', 'Standard Reports')
@section('heading', 'Standard Ministry Reports (View, PDF & Excel)')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @foreach($reports as $report)
    <div class="rounded-lg bg-white border border-slate-200 p-5 shadow-sm flex flex-col justify-between gap-4">
        <div>
            <h3 class="text-base font-semibold text-slate-900">{{ $report['name'] }}</h3>
            <p class="mt-1 text-sm text-slate-600">{{ $report['description'] }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('reports.standard.show', $report['key']) }}"
               class="rounded-md bg-slate-800 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-700">
                View Report
            </a>
            @if(auth()->user()->can('reports.export'))
                <a href="{{ route('reports.standard.download', [$report['key'], 'pdf']) }}"
                   class="rounded-md bg-rose-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-800">
                    Download PDF
                </a>
                <a href="{{ route('reports.standard.download', [$report['key'], 'xlsx']) }}"
                   class="rounded-md bg-emerald-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-800">
                    Download Excel (.xlsx)
                </a>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endsection
