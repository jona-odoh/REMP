@props(['documents', 'documentableType', 'documentableId', 'categories' => ['Photograph', 'Survey / Map', 'Historical Record', 'Audio / Oral History', 'Video', 'Official Memo']])

<div class="rounded-lg bg-white border border-slate-200 shadow-sm p-5 space-y-4">
    <div class="flex items-center justify-between">
        <h3 class="text-base font-semibold text-slate-900">Attached Media &amp; Documents ({{ $documents->count() }})</h3>
        <span class="text-xs text-slate-500">Stored locally on Ministry server</span>
    </div>

    @can('create', \App\Models\Document::class)
    <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data"
          class="rounded-md bg-slate-50 border border-slate-200 p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        @csrf
        <input type="hidden" name="documentable_type" value="{{ $documentableType }}">
        <input type="hidden" name="documentable_id" value="{{ $documentableId }}">

        <input name="title" type="text" required placeholder="Document / Media Title *"
               class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm">
        <select name="category" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm">
            @foreach($categories as $cat)
                <option value="{{ $cat }}">{{ $cat }}</option>
            @endforeach
        </select>
        <input name="document_date" type="date" value="{{ now()->toDateString() }}"
               class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm">
        <input name="file" type="file" required
               class="text-xs text-slate-600 file:mr-2 file:rounded file:border-0 file:bg-emerald-50 file:px-2.5 file:py-1.5 file:text-xs file:font-semibold file:text-emerald-800 hover:file:bg-emerald-100">
        <button type="submit" class="rounded-md bg-emerald-700 px-3 py-1.5 text-sm font-semibold text-white hover:bg-emerald-800">
            Upload File
        </button>
    </form>
    @endcan

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-3 py-2">Title &amp; File</th>
                    <th class="px-3 py-2">Type / Category</th>
                    <th class="px-3 py-2">Date</th>
                    <th class="px-3 py-2">Size</th>
                    <th class="px-3 py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($documents as $doc)
                <tr class="hover:bg-slate-50">
                    <td class="px-3 py-2.5">
                        <div class="font-medium text-slate-900">{{ $doc->title }}</div>
                        <div class="text-xs text-slate-500">{{ $doc->original_filename }} · by {{ $doc->uploader?->name ?? 'System' }}</div>
                    </td>
                    <td class="px-3 py-2.5">
                        <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">{{ $doc->file_type->label() }}</span>
                        <span class="ml-1 text-xs text-slate-500">{{ $doc->category }}</span>
                    </td>
                    <td class="px-3 py-2.5 text-xs text-slate-600">{{ \App\Support\Format::date($doc->document_date ?? $doc->created_at) }}</td>
                    <td class="px-3 py-2.5 text-xs text-slate-600">{{ $doc->formattedSize() }}</td>
                    <td class="px-3 py-2.5 text-right space-x-2">
                        <a href="{{ route('documents.download', $doc) }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-900">Download</a>
                        @can('delete', $doc)
                        <form method="POST" action="{{ route('documents.destroy', $doc) }}" class="inline" onsubmit="return confirm('Delete this file permanently?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs font-medium text-rose-700 hover:text-rose-900">Delete</button>
                        </form>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-3 py-6 text-center text-slate-500 text-xs">No media or documents attached yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
