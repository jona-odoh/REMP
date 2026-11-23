@extends('layouts.app')

@section('title', 'Standard Reports')
@section('heading', 'Standard Ministry Reports (PDF & Excel)')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @foreach($reports as $report)
    <div class="rounded-lg bg-white border border-slate-200 p-5 shadow-sm flex flex-col justify-between gap-4">
        <div>
            <h3 class="text-base font-semibold text-slate-900">{{ $report['name'] }}</h3>
            <p class="mt-1 text-sm text-slate-600">{{ $report['description'] }}</p>
        </div>
        @if(auth()->user()->can('reports.export'))
        <div class="flex items-center gap-2">
            <a href="{{ route('reports.standard.download', [$report['key'], 'pdf']) }}"
               class="rounded-md bg-rose-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-800">
                Download PDF
            </a>
            <a href="{{ route('reports.standard.download', [$report['key'], 'xlsx']) }}"
               class="rounded-md bg-emerald-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-800">
                Download Excel (.xlsx)
            </a>
        </div>
        @else
        <p class="text-xs text-slate-500">Export requires Executive or Department Head permissions.</p>
        @endif
    </div>
    @endforeach
</div>
@endsection
