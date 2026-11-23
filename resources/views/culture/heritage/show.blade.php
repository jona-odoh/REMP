@extends('layouts.app')

@section('title', $heritage->name)
@section('heading', $heritage->name)

@section('content')
<div class="flex flex-wrap items-center justify-between gap-2">
    <a href="{{ route('culture.heritage.index') }}" class="text-sm font-medium text-emerald-700 hover:text-emerald-900">← Back to Intangible Heritage</a>
    <div class="flex items-center gap-2">
        @can('update', $heritage)
        <a href="{{ route('culture.heritage.edit', $heritage) }}" class="rounded-md bg-emerald-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-800">Edit Record</a>
        @endcan
        @can('delete', $heritage)
        <form method="POST" action="{{ route('culture.heritage.destroy', $heritage) }}" onsubmit="return confirm('Delete this intangible heritage record?')">
            @csrf @method('DELETE')
            <button type="submit" class="rounded-md border border-rose-300 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-800 hover:bg-rose-100">Delete</button>
        </form>
        @endcan
    </div>
</div>

<div class="rounded-lg bg-white border border-slate-200 shadow-sm p-6 space-y-4">
    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $heritage->safeguarding_status->badgeColor() }}">
        {{ $heritage->safeguarding_status->label() }}
    </span>

    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
        <div>
            <dt class="text-xs uppercase text-slate-500">Primary LGA</dt>
            <dd class="mt-1 font-medium text-slate-900">{{ $heritage->lga?->name ?? 'Statewide' }}</dd>
        </div>
        <div>
            <dt class="text-xs uppercase text-slate-500">Festival Season</dt>
            <dd class="mt-1 font-medium text-slate-900">{{ $heritage->festival_season }}</dd>
        </div>
        <div class="sm:col-span-2">
            <dt class="text-xs uppercase text-slate-500">Participating Communities</dt>
            <dd class="mt-1 text-slate-800">{{ $heritage->participating_communities }}</dd>
        </div>
    </dl>

    <div class="pt-3 border-t border-slate-200">
        <h3 class="text-xs font-semibold uppercase text-slate-500">Historical &amp; Cultural Description</h3>
        <p class="mt-1 text-sm text-slate-700 whitespace-pre-line">{{ $heritage->historical_description }}</p>
    </div>
</div>

<x-documents-panel :documents="$heritage->documents" documentable-type="intangible_heritage" :documentable-id="$heritage->id" />
@endsection
