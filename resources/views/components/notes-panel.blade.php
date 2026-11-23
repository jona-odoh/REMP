@props(['notes', 'notableType', 'notableId'])

<div class="rounded-lg bg-white border border-slate-200 shadow-sm p-5 space-y-4">
    <h3 class="text-base font-semibold text-slate-900">Internal Officer Notes ({{ $notes->count() }})</h3>

    @can('create', \App\Models\Note::class)
    <form method="POST" action="{{ route('notes.store') }}" class="space-y-2">
        @csrf
        <input type="hidden" name="notable_type" value="{{ $notableType }}">
        <input type="hidden" name="notable_id" value="{{ $notableId }}">
        <textarea name="body" rows="2" required placeholder="Add an internal Ministry note or remark..."
                  class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm"></textarea>
        <div class="flex justify-end">
            <button type="submit" class="rounded-md bg-slate-800 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-700">Post Note</button>
        </div>
    </form>
    @endcan

    <div class="space-y-3">
        @forelse($notes as $note)
        <div class="rounded-md border border-slate-200 p-3 text-sm space-y-2" x-data="{ replying: false }">
            <div class="flex items-center justify-between text-xs text-slate-500">
                <span class="font-semibold text-slate-900">{{ $note->author?->name ?? 'Officer' }}</span>
                <span>{{ \App\Support\Format::datetime($note->created_at) }}</span>
            </div>
            <p class="text-slate-800 whitespace-pre-line">{{ $note->body }}</p>

            <div class="flex items-center gap-3 text-xs">
                @can('create', \App\Models\Note::class)
                <button type="button" @click="replying = !replying" class="font-medium text-emerald-700 hover:underline">Reply</button>
                @endcan
                @can('delete', $note)
                <form method="POST" action="{{ route('notes.destroy', $note) }}" class="inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-rose-700 hover:underline">Delete</button>
                </form>
                @endcan
            </div>

            @can('create', \App\Models\Note::class)
            <form x-show="replying" x-cloak method="POST" action="{{ route('notes.store') }}" class="pt-2 space-y-2">
                @csrf
                <input type="hidden" name="notable_type" value="{{ $notableType }}">
                <input type="hidden" name="notable_id" value="{{ $notableId }}">
                <input type="hidden" name="parent_id" value="{{ $note->id }}">
                <textarea name="body" rows="2" required placeholder="Write a reply..." class="block w-full rounded border border-slate-300 px-2.5 py-1.5 text-xs"></textarea>
                <button type="submit" class="rounded bg-emerald-700 px-2.5 py-1 text-xs font-semibold text-white hover:bg-emerald-800">Post Reply</button>
            </form>
            @endcan

            @if($note->replies->isNotEmpty())
            <div class="ml-5 pl-3 border-l-2 border-slate-200 space-y-2">
                @foreach($note->replies as $reply)
                <div class="rounded bg-slate-50 p-2.5 text-xs">
                    <div class="flex justify-between text-slate-500 mb-1">
                        <span class="font-semibold text-slate-800">{{ $reply->author?->name ?? 'Officer' }}</span>
                        <span>{{ \App\Support\Format::datetime($reply->created_at) }}</span>
                    </div>
                    <p class="text-slate-700">{{ $reply->body }}</p>
                </div>
                @endforeach
            </div>
            @endif
        </div>
        @empty
        <p class="text-xs text-slate-500">No internal notes recorded on this record yet.</p>
        @endforelse
    </div>
</div>
