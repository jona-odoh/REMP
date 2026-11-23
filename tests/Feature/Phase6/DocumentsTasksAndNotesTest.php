<?php

namespace Tests\Feature\Phase6;

use App\Enums\AuditAction;
use App\Enums\TaskStatus;
use App\Models\CulturalAsset;
use App\Models\Document;
use App\Models\Note;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskDueReminderNotification;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentsTasksAndNotesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_document_repository_search_and_view_access_audit_logging(): void
    {
        Storage::fake('local');
        $director = User::where('email', 'director@ksmct.kogistate.gov.ng')->firstOrFail();
        $admin = User::where('email', 'admin')->firstOrFail();

        $file = UploadedFile::fake()->create('kogi_tourism_masterplan.pdf', 200, 'application/pdf');

        $this->actingAs($admin)
            ->post(route('documents.store'), [
                'title' => 'Kogi State Tourism Masterplan 2026',
                'description' => 'Strategic policy framework for confluence eco-tourism development.',
                'category' => 'Policy',
                'file' => $file,
            ])
            ->assertRedirect();

        $doc = Document::where('title', 'Kogi State Tourism Masterplan 2026')->firstOrFail();

        // Search repository
        $this->actingAs($director)
            ->get(route('documents.index', ['search' => 'Masterplan']))
            ->assertOk()
            ->assertSee('Kogi State Tourism Masterplan 2026');

        // Viewing document metadata must log AuditAction::VIEW
        $this->actingAs($director)
            ->get(route('documents.show', $doc))
            ->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $director->id,
            'action' => AuditAction::VIEW->value,
            'entity_type' => Document::class,
            'entity_id' => $doc->id,
        ]);
    }

    public function test_task_assignment_notifications_and_daily_due_date_reminder_command(): void
    {
        $director = User::where('email', 'director@ksmct.kogistate.gov.ng')->firstOrFail();
        $officer = User::where('email', 'licensing@ksmct.kogistate.gov.ng')->firstOrFail();

        $this->actingAs($director)
            ->post(route('tasks.store'), [
                'title' => 'Follow up on Idah Lodge overdue inspection',
                'description' => 'Coordinate field team before Friday.',
                'assigned_to' => $officer->id,
                'due_date' => today()->addDay()->toDateString(),
            ])
            ->assertRedirect(route('tasks.index'));

        $task = Task::where('title', 'Follow up on Idah Lodge overdue inspection')->firstOrFail();
        $this->assertSame(TaskStatus::PENDING, $task->status);

        // Assignment notification sent to database channel
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $officer->id,
            'type' => TaskDueReminderNotification::class,
        ]);

        // Daily due-date reminder command sends reminder once per day
        $this->artisan('ksmct:send-task-reminders')->assertSuccessful();
        $afterFirst = DB::table('notifications')->where('notifiable_id', $officer->id)->count();
        $this->assertSame(2, $afterFirst); // 1 assigned + 1 due_reminder

        $this->artisan('ksmct:send-task-reminders')->assertSuccessful();
        $this->assertSame($afterFirst, DB::table('notifications')->where('notifiable_id', $officer->id)->count());

        // Assignee can mark task completed and mark notifications read
        $this->actingAs($officer)
            ->patch(route('tasks.status', $task), ['status' => TaskStatus::COMPLETED->value])
            ->assertRedirect();

        $this->assertSame(TaskStatus::COMPLETED, $task->fresh()->status);
        $this->assertNotNull($task->fresh()->completed_at);

        $this->actingAs($officer)
            ->post(route('notifications.read-all'))
            ->assertRedirect();

        $this->assertSame(0, $officer->fresh()->unreadNotifications()->count());
    }

    public function test_threaded_internal_notes_on_record_detail_pages(): void
    {
        $cultureHead = User::where('email', 'culture.head@ksmct.kogistate.gov.ng')->firstOrFail();
        $director = User::where('email', 'director@ksmct.kogistate.gov.ng')->firstOrFail();
        $asset = CulturalAsset::firstOrFail();

        // Top-level note
        $this->actingAs($cultureHead)
            ->post(route('notes.store'), [
                'notable_type' => 'cultural_asset',
                'notable_id' => $asset->id,
                'body' => 'Requested perimeter fencing quote from Works Department.',
            ])
            ->assertRedirect();

        $parent = Note::where('notable_id', $asset->id)->whereNull('parent_id')->firstOrFail();

        // Threaded reply
        $this->actingAs($director)
            ->post(route('notes.store'), [
                'notable_type' => 'cultural_asset',
                'notable_id' => $asset->id,
                'parent_id' => $parent->id,
                'body' => 'Approved — submit BOQ by Monday.',
            ])
            ->assertRedirect();

        $this->assertSame(1, $parent->replies()->count());

        $this->actingAs($director)
            ->get(route('culture.assets.show', $asset))
            ->assertOk()
            ->assertSee('Requested perimeter fencing quote from Works Department.')
            ->assertSee('Approved — submit BOQ by Monday.');
    }
}
