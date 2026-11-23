@extends('layouts.app')

@section('title', 'Notification Center')
@section('heading', 'In-App Notification Center')

@section('content')
<div class="flex justify-end">
    <form method="POST" action="{{ route('notifications.read-all') }}">
        @csrf
        <button type="submit" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
            Mark All as Read
        </button>
    </form>
</div>

<div class="rounded-lg bg-white border border-slate-200 shadow-sm divide-y divide-slate-200">
    @forelse($notifications as $notification)
    <div class="p-4 flex items-start justify-between gap-4 {{ $notification->read_at ? 'bg-white' : 'bg-emerald-50/50' }}">
        <div class="space-y-1 text-sm">
            <div class="font-semibold text-slate-900">{{ $notification->data['title'] ?? 'Notification' }}</div>
            <p class="text-slate-600 text-xs">{{ $notification->data['message'] ?? '' }}</p>
            <div class="text-[11px] text-slate-400">{{ \App\Support\Format::datetime($notification->created_at) }}</div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            @if(!empty($notification->data['url']))
                <a href="{{ $notification->data['url'] }}" class="text-xs font-semibold text-emerald-700 hover:underline">Open →</a>
            @endif
            @if(!$notification->read_at)
                <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                    @csrf
                    <button type="submit" class="rounded border border-slate-300 px-2 py-1 text-xs text-slate-600 hover:bg-slate-100">Mark Read</button>
                </form>
            @endif
        </div>
    </div>
    @empty
    <div class="p-8 text-center text-sm text-slate-500">You have no notifications.</div>
    @endforelse
</div>

<div>{{ $notifications->links() }}</div>
@endsection
