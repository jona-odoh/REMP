@extends('layouts.app')

@section('title', 'Tasks & Assignments')
@section('heading', 'Ministry Task Assignment & Follow-Up')

@section('content')
@can('create', \App\Models\Task::class)
<div class="rounded-lg bg-white border border-slate-200 shadow-sm p-5">
    <h3 class="text-sm font-semibold text-slate-900 mb-3">Assign New Task</h3>
    <form method="POST" action="{{ route('tasks.store') }}" class="grid grid-cols-1 sm:grid-cols-6 gap-3">
        @csrf
        <input name="title" type="text" placeholder="Task Title *" required class="sm:col-span-2 rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <select name="assigned_to" required class="sm:col-span-2 rounded-md border border-slate-300 px-3 py-1.5 text-sm">
            <option value="">Assign To Officer *</option>
            @foreach($officers as $off)
                <option value="{{ $off->id }}">{{ $off->name }}</option>
            @endforeach
        </select>
        <input name="due_date" type="date" value="{{ now()->addDays(3)->toDateString() }}" required class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <button type="submit" class="rounded-md bg-emerald-700 px-3 py-1.5 text-sm font-semibold text-white hover:bg-emerald-800">Assign</button>
        <input name="description" type="text" placeholder="Task instructions / notes" class="sm:col-span-6 rounded-md border border-slate-300 px-3 py-1.5 text-sm">
    </form>
</div>
@endcan

<div class="flex flex-wrap items-center gap-2">
    <a href="{{ route('tasks.index') }}" class="rounded-md px-3 py-1.5 text-xs font-semibold {{ !request()->boolean('mine') ? 'bg-slate-800 text-white' : 'bg-white border border-slate-300 text-slate-700' }}">All Tasks</a>
    <a href="{{ route('tasks.index', ['mine' => 1]) }}" class="rounded-md px-3 py-1.5 text-xs font-semibold {{ request()->boolean('mine') ? 'bg-slate-800 text-white' : 'bg-white border border-slate-300 text-slate-700' }}">Assigned to Me</a>
</div>

<div class="rounded-lg bg-white border border-slate-200 shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
            <tr>
                <th class="px-4 py-3">Task</th>
                <th class="px-4 py-3">Assigned To</th>
                <th class="px-4 py-3">Due Date</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 text-right">Update Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($tasks as $task)
            <tr class="{{ $task->isOverdue() ? 'bg-rose-50/50' : 'hover:bg-slate-50' }}">
                <td class="px-4 py-3">
                    <div class="font-semibold text-slate-900">{{ $task->title }}</div>
                    @if($task->description)<div class="text-xs text-slate-500">{{ $task->description }}</div>@endif
                </td>
                <td class="px-4 py-3 text-xs text-slate-700">{{ $task->assignee?->name }}</td>
                <td class="px-4 py-3 text-xs {{ $task->isOverdue() ? 'font-bold text-rose-700' : 'text-slate-600' }}">
                    {{ \App\Support\Format::date($task->due_date) }} @if($task->isOverdue()) (OVERDUE) @endif
                </td>
                <td class="px-4 py-3">
                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $task->status->badgeColor() }}">{{ $task->status->label() }}</span>
                </td>
                <td class="px-4 py-3 text-right">
                    @can('update', $task)
                    <form method="POST" action="{{ route('tasks.status', $task) }}" class="inline-flex items-center gap-1">
                        @csrf @method('PATCH')
                        <select name="status" class="rounded border border-slate-300 px-2 py-1 text-xs">
                            @foreach($statuses as $st)
                                <option value="{{ $st->value }}" @selected($task->status === $st)>{{ $st->label() }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="rounded bg-slate-800 px-2 py-1 text-xs font-semibold text-white hover:bg-slate-700">Save</button>
                    </form>
                    @endcan
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">No tasks assigned.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-4 py-3 border-t border-slate-200">{{ $tasks->links() }}</div>
</div>
@endsection
