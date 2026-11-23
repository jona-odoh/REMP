<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Http\Requests\Tasks\StoreTaskRequest;
use App\Http\Requests\Tasks\UpdateTaskStatusRequest;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskDueReminderNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Task::class);

        $tasks = Task::query()
            ->with(['assignee', 'assigner'])
            ->when($request->boolean('mine'), fn ($q) => $q->where('assigned_to', $request->user()->getKey()))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->orderBy('due_date')
            ->paginate(20)
            ->withQueryString();

        $officers = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('tasks.index', [
            'tasks' => $tasks,
            'officers' => $officers,
            'statuses' => TaskStatus::cases(),
        ]);
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $task = Task::create($request->validated() + [
            'assigned_by' => $request->user()->getKey(),
            'status' => TaskStatus::PENDING,
        ]);

        $task->load('assignee');
        if ($task->assignee) {
            $task->assignee->notify(new TaskDueReminderNotification($task, 'assigned'));
        }

        return redirect()->route('tasks.index')->with('status', "Task '{$task->title}' assigned to {$task->assignee?->name}.");
    }

    public function updateStatus(UpdateTaskStatusRequest $request, Task $task): RedirectResponse
    {
        $status = TaskStatus::from($request->validated('status'));

        $task->update([
            'status' => $status,
            'completed_at' => $status === TaskStatus::COMPLETED ? now() : null,
        ]);

        return back()->with('status', "Task '{$task->title}' updated to {$status->label()}.");
    }

    public function destroy(Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return back()->with('status', 'Task deleted.');
    }
}
