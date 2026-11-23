@extends('layouts.app')

@section('title', 'Inspection #'.$inspection->id)
@section('heading', 'Inspection: '.$inspection->hotel->name)

@section('content')
<div class="flex items-center justify-between">
    <a href="{{ route('licensing.inspections.index') }}" class="text-sm font-medium text-emerald-700 hover:text-emerald-900">← Back to Inspections</a>
    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $inspection->status->badgeColor() }}">
        {{ $inspection->status->label() }}
    </span>
</div>

<div class="rounded-lg bg-white border border-slate-200 shadow-sm p-6 space-y-4">
    <dl class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-sm">
        <div><dt class="text-xs uppercase text-slate-500">Hotel</dt><dd class="mt-1 font-semibold">{{ $inspection->hotel->name }}</dd></div>
        <div><dt class="text-xs uppercase text-slate-500">Assigned Officer</dt><dd class="mt-1">{{ $inspection->assignedOfficer?->name }}</dd></div>
        <div><dt class="text-xs uppercase text-slate-500">Scheduled Date</dt><dd class="mt-1">{{ \App\Support\Format::date($inspection->scheduled_date) }}</dd></div>
        <div><dt class="text-xs uppercase text-slate-500">Completed Date</dt><dd class="mt-1">{{ \App\Support\Format::date($inspection->completed_date) }}</dd></div>
    </dl>

    @if($inspection->status !== \App\Enums\InspectionStatus::COMPLETED && auth()->user()->can('update', $inspection))
    <form method="POST" action="{{ route('licensing.inspections.complete', $inspection) }}" class="pt-4 border-t border-slate-200 space-y-4">
        @csrf
        <h3 class="text-sm font-semibold text-slate-900">Complete Inspection Checklist</h3>

        <div class="space-y-3">
            @foreach($checklistTemplates as $idx => $item)
            <div class="rounded-md border border-slate-200 p-3 grid grid-cols-1 sm:grid-cols-12 gap-3 items-center text-sm">
                <input type="hidden" name="checklist[{{ $idx }}][template_id]" value="{{ $item->id }}">
                <div class="sm:col-span-6">
                    <span class="text-xs font-semibold uppercase text-slate-500">{{ $item->section }}</span>
                    @if($item->is_mandatory)<span class="ml-1 text-[10px] rounded bg-amber-100 px-1.5 py-0.5 text-amber-800">Mandatory</span>@endif
                    <p class="text-slate-900">{{ $item->item_text }}</p>
                </div>
                <div class="sm:col-span-2">
                    <select name="checklist[{{ $idx }}][passed]" class="w-full rounded-md border border-slate-300 px-2 py-1 text-xs">
                        <option value="1">Pass</option>
                        <option value="0">Fail</option>
                    </select>
                </div>
                <div class="sm:col-span-4">
                    <input name="checklist[{{ $idx }}][remark]" type="text" placeholder="Officer remark (optional)" class="w-full rounded-md border border-slate-300 px-2.5 py-1 text-xs">
                </div>
            </div>
            @endforeach
        </div>

        <div>
            <label for="notes" class="block text-sm font-medium text-slate-700">Overall Field Notes</label>
            <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">{{ old('notes', $inspection->notes) }}</textarea>
        </div>

        <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">
            Submit Completed Inspection
        </button>
    </form>
    @elseif($inspection->checklist_results)
    <div class="pt-4 border-t border-slate-200 space-y-2">
        <h3 class="text-sm font-semibold text-slate-900">Recorded Checklist Results</h3>
        @foreach($inspection->checklist_results as $row)
        <div class="flex items-center justify-between rounded-md border border-slate-200 px-3 py-2 text-xs">
            <div>
                <span class="font-semibold text-slate-700">[{{ $row['section'] ?? 'General' }}]</span>
                <span>{{ $row['item_text'] ?? '' }}</span>
                @if(!empty($row['remark']))<span class="text-slate-500"> — {{ $row['remark'] }}</span>@endif
            </div>
            <span class="rounded-full px-2 py-0.5 font-semibold {{ !empty($row['passed']) ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                {{ !empty($row['passed']) ? 'Passed' : 'Failed' }}
            </span>
        </div>
        @endforeach
    </div>
    @endif
</div>

<x-documents-panel :documents="$inspection->documents" documentable-type="inspection" :documentable-id="$inspection->id" :categories="['Inspection Photo', 'Field Report']" />
@endsection
