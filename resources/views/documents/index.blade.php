@extends('layouts.app')

@section('title', 'Document Repository')
@section('heading', 'Central Ministry Document Repository')

@section('content')
@can('create', \App\Models\Document::class)
<div class="rounded-lg bg-white border border-slate-200 shadow-sm p-5">
    <h3 class="text-sm font-semibold text-slate-900 mb-3">Upload Central Ministry Document (Local Storage)</h3>
    <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-6 gap-3">
        @csrf
        <input name="title" type="text" placeholder="Document Title *" required class="sm:col-span-2 rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <input name="category" type="text" placeholder="Category (e.g. Policy, Memo)" value="Official Memo" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <input name="document_date" type="date" value="{{ now()->toDateString() }}" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <input name="file" type="file" required class="sm:col-span-2 text-xs text-slate-700">
        <input name="description" type="text" placeholder="Description / Keywords (indexed for full-text search)" class="sm:col-span-5 rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <button type="submit" class="rounded-md bg-emerald-700 px-3 py-1.5 text-sm font-semibold text-white hover:bg-emerald-800">Upload</button>
    </form>
</div>
@endcan

<form method="GET" action="{{ route('documents.index') }}" class="flex flex-wrap items-center gap-2">
    <input type="text" name="search" value="{{ $search }}" placeholder="Full-text search title &amp; description..." class="w-72 rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm">
    <select name="category" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm">
        <option value="">All Categories</option>
        @foreach($categories as $cat)
            <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ $cat }}</option>
        @endforeach
    </select>
    <select name="file_type" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm">
        <option value="">All File Types</option>
        @foreach($fileTypes as $ft)
            <option value="{{ $ft->value }}" @selected(request('file_type') === $ft->value)>{{ $ft->label() }}</option>
        @endforeach
    </select>
    <button type="submit" class="rounded-md bg-slate-800 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-700">Search</button>
</form>

<div class="rounded-lg bg-white border border-slate-200 shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
            <tr>
                <th class="px-4 py-3">Document Title</th>
                <th class="px-4 py-3">Category / Type</th>
                <th class="px-4 py-3">Linked Record</th>
                <th class="px-4 py-3">Date / Size</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($documents as $doc)
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-3">
                    <a href="{{ route('documents.show', $doc) }}" class="font-semibold text-emerald-800 hover:underline">{{ $doc->title }}</a>
                    <div class="text-xs text-slate-500">{{ $doc->original_filename }} · by {{ $doc->uploader?->name ?? 'System' }}</div>
                </td>
                <td class="px-4 py-3 text-xs">
                    <span class="rounded-full bg-slate-100 px-2 py-0.5 font-medium">{{ $doc->file_type->label() }}</span>
                    <span class="ml-1 text-slate-600">{{ $doc->category }}</span>
                </td>
                <td class="px-4 py-3 text-xs text-slate-600">
                    {{ $doc->documentable_type ? class_basename($doc->documentable_type).' #'.$doc->documentable_id : 'Central Repository' }}
                </td>
                <td class="px-4 py-3 text-xs text-slate-600">{{ \App\Support\Format::date($doc->document_date ?? $doc->created_at) }} · {{ $doc->formattedSize() }}</td>
                <td class="px-4 py-3 text-right space-x-2">
                    <a href="{{ route('documents.show', $doc) }}" class="text-xs font-medium text-slate-700 hover:underline">Inspect</a>
                    <a href="{{ route('documents.download', $doc) }}" class="text-xs font-semibold text-emerald-700 hover:underline">Download</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">No documents matched your search.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-4 py-3 border-t border-slate-200">{{ $documents->links() }}</div>
</div>
@endsection
