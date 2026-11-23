@extends('layouts.app')

@section('title', 'Document Intangible Heritage')
@section('heading', 'Document Intangible Heritage / Festival')

@section('content')
<div class="max-w-3xl rounded-lg bg-white border border-slate-200 shadow-sm p-6">
    <form method="POST" action="{{ route('culture.heritage.store') }}" x-data="formAutosave('ksmct_intangible_heritage_create')" class="space-y-4">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label for="name" class="block text-sm font-medium text-slate-700">Festival / Tradition Name *</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required
                       class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label for="lga_id" class="block text-sm font-medium text-slate-700">Primary LGA</label>
                <select id="lga_id" name="lga_id" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Statewide / Multiple LGAs</option>
                    @foreach($lgas as $lga)
                        <option value="{{ $lga->id }}" @selected((string) old('lga_id') === (string) $lga->id)>{{ $lga->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="festival_season" class="block text-sm font-medium text-slate-700">Festival Season / Period *</label>
                <input id="festival_season" name="festival_season" type="text" value="{{ old('festival_season') }}" required placeholder="e.g. April (Annually)"
                       class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div class="sm:col-span-2">
                <label for="safeguarding_status" class="block text-sm font-medium text-slate-700">Safeguarding Status *</label>
                <select id="safeguarding_status" name="safeguarding_status" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}" @selected(old('safeguarding_status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <label for="participating_communities" class="block text-sm font-medium text-slate-700">Participating Communities *</label>
                <textarea id="participating_communities" name="participating_communities" rows="2" required
                          class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">{{ old('participating_communities') }}</textarea>
            </div>

            <div class="sm:col-span-2">
                <label for="historical_description" class="block text-sm font-medium text-slate-700">Historical &amp; Cultural Description *</label>
                <textarea id="historical_description" name="historical_description" rows="5" required
                          class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">{{ old('historical_description') }}</textarea>
            </div>
        </div>

        <div class="pt-3 flex items-center gap-3">
            <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">
                Save Record
            </button>
            <a href="{{ route('culture.heritage.index') }}" class="text-sm text-slate-600 hover:text-slate-900">Cancel</a>
        </div>
    </form>
</div>
@endsection
