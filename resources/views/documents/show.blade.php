@extends('layouts.app')

@section('title', $document->title)
@section('heading', 'Document Metadata: '.$document->title)

@section('content')
<div class="max-w-2xl space-y-4">
    <a href="{{ route('documents.index') }}" class="text-sm font-medium text-emerald-700 hover:text-emerald-900">← Back to Document Repository</a>

    <div class="rounded-lg bg-white border border-slate-200 shadow-sm p-6 space-y-4 text-sm">
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div><dt class="text-xs uppercase text-slate-500">Original Filename</dt><dd class="mt-1 font-mono text-xs">{{ $document->original_filename }}</dd></div>
            <div><dt class="text-xs uppercase text-slate-500">Type / Category</dt><dd class="mt-1">{{ $document->file_type->label() }} · {{ $document->category }}</dd></div>
            <div><dt class="text-xs uppercase text-slate-500">Document Date</dt><dd class="mt-1">{{ \App\Support\Format::date($document->document_date ?? $document->created_at) }}</dd></div>
            <div><dt class="text-xs uppercase text-slate-500">File Size / MIME</dt><dd class="mt-1">{{ $document->formattedSize() }} ({{ $document->mime_type }})</dd></div>
            <div><dt class="text-xs uppercase text-slate-500">Uploaded By</dt><dd class="mt-1">{{ $document->uploader?->name ?? 'System' }}</dd></div>
        </dl>

        @if($document->description)
        <div class="pt-3 border-t border-slate-200">
            <dt class="text-xs uppercase text-slate-500">Description</dt>
            <p class="mt-1 text-slate-700">{{ $document->description }}</p>
        </div>
        @endif

        <div class="pt-3 border-t border-slate-200 flex items-center justify-between">
            <span class="text-xs text-slate-500">Document view &amp; download events are logged in the audit trail.</span>
            <a href="{{ route('documents.download', $document) }}" class="rounded-md bg-emerald-700 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-800">Download File</a>
        </div>
    </div>
</div>
@endsection
