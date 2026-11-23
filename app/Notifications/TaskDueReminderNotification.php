<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskDueReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Task $task,
        public readonly string $reason = 'assigned',
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $title = $this->reason === 'due_reminder'
            ? "Task Due Soon: {$this->task->title}"
            : "New Task Assigned: {$this->task->title}";

        return [
            'title' => $title,
            'message' => "Due on {$this->task->due_date->format('d/m/Y')} (Status: {$this->task->status->label()}).",
            'task_id' => $this->task->id,
            'reason' => $this->reason,
            'url' => route('tasks.index'),
        ];
    }
}
