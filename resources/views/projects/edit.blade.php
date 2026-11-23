@extends('layouts.app')

@section('title', 'Edit '.$project->title)
@section('heading', 'Edit Project: '.$project->title)

@section('content')
<div class="max-w-3xl rounded-lg bg-white border border-slate-200 shadow-sm p-6">
    <form method="POST" action="{{ route('projects.update', $project) }}" class="space-y-4">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label for="title" class="block text-sm font-medium text-slate-700">Project Title *</label>
                <input id="title" name="title" type="text" value="{{ old('title', $project->title) }}" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label for="lga_id" class="block text-sm font-medium text-slate-700">Target LGA</label>
                <select id="lga_id" name="lga_id" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Statewide</option>
                    @foreach($lgas as $lga)
                        <option value="{{ $lga->id }}" @selected((string) old('lga_id', $project->lga_id) === (string) $lga->id)>{{ $lga->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-slate-700">Status *</label>
                <select id="status" name="status" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    @foreach($statuses as $st)
                        <option value="{{ $st->value }}" @selected(old('status', $project->status->value) === $st->value)>{{ $st->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="budget_line" class="block text-sm font-medium text-slate-700">Budget Line *</label>
                <input id="budget_line" name="budget_line" type="text" value="{{ old('budget_line', $project->budget_line) }}" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label for="funding_source" class="block text-sm font-medium text-slate-700">Funding Source *</label>
                <input id="funding_source" name="funding_source" type="text" value="{{ old('funding_source', $project->funding_source) }}" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div class="sm:col-span-2">
                <label for="implementing_partner" class="block text-sm font-medium text-slate-700">Implementing Partner</label>
                <input id="implementing_partner" name="implementing_partner" type="text" value="{{ old('implementing_partner', $project->implementing_partner) }}" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label for="start_date" class="block text-sm font-medium text-slate-700">Start Date *</label>
                <input id="start_date" name="start_date" type="date" value="{{ old('start_date', $project->start_date?->toDateString()) }}" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-slate-700">End Date *</label>
                <input id="end_date" name="end_date" type="date" value="{{ old('end_date', $project->end_date?->toDateString()) }}" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div class="sm:col-span-2">
                <label for="description" class="block text-sm font-medium text-slate-700">Description</label>
                <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">{{ old('description', $project->description) }}</textarea>
            </div>
        </div>

        <div class="pt-3 flex items-center gap-3">
            <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Save Changes</button>
            <a href="{{ route('projects.show', $project) }}" class="text-sm text-slate-600 hover:text-slate-900">Cancel</a>
        </div>
    </form>
</div>
@endsection
