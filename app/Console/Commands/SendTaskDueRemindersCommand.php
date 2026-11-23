<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskDueReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendTaskDueRemindersCommand extends Command
{
    protected $signature = 'ksmct:send-task-reminders';

    protected $description = 'Send daily in-app due-date reminders for open Ministry tasks due within 2 days or overdue.';

    public function handle(): int
    {
        $tasks = Task::query()
            ->with('assignee')
            ->open()
            ->whereDate('due_date', '<=', today()->addDays(2))
            ->get();

        $sent = 0;

        foreach ($tasks as $task) {
            if (! $task->assignee || ! $task->assignee->is_active) {
                continue;
            }

            $alreadySentToday = DB::table('notifications')
                ->where('notifiable_type', User::class)
                ->where('notifiable_id', $task->assigned_to)
                ->where('type', TaskDueReminderNotification::class)
                ->where('data', 'like', '%"task_id":'.$task->id.'%')
                ->where('data', 'like', '%"reason":"due_reminder"%')
                ->whereDate('created_at', today())
                ->exists();

            if (! $alreadySentToday) {
                $task->assignee->notify(new TaskDueReminderNotification($task, 'due_reminder'));
                $sent++;
            }
        }

        $this->info("Task due-date reminders sent: {$sent}");

        return self::SUCCESS;
    }
}
