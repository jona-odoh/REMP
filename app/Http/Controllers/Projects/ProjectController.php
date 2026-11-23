<?php

namespace App\Http\Controllers\Projects;

use App\Enums\ProjectStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Projects\SaveProjectRequest;
use App\Http\Requests\Projects\StoreMilestoneRequest;
use App\Http\Requests\Projects\StoreProjectExpenditureRequest;
use App\Models\Lga;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Project::class);

        $filters = $request->only(['search', 'lga_id', 'status']);
        $threshold = (float) SystemSetting::getValue('budget_variance_threshold_percent', 15);

        $projects = Project::query()
            ->with(['lga', 'expenditures', 'milestones'])
            ->filter($filters)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $flaggedProjects = Project::with('expenditures', 'lga')
            ->get()
            ->filter(fn (Project $p) => $p->hasVarianceAlert($threshold));

        return view('projects.index', [
            'projects' => $projects,
            'flaggedProjects' => $flaggedProjects,
            'threshold' => $threshold,
            'lgas' => Lga::active()->get(),
            'statuses' => ProjectStatus::cases(),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Project::class);

        return view('projects.create', [
            'lgas' => Lga::active()->get(),
            'statuses' => ProjectStatus::cases(),
        ]);
    }

    public function store(SaveProjectRequest $request): RedirectResponse
    {
        $project = Project::create($request->validated() + [
            'created_by' => $request->user()->getKey(),
            'updated_by' => $request->user()->getKey(),
        ]);

        return redirect()->route('projects.show', $project)->with('status', "Project '{$project->title}' registered.");
    }

    public function show(Project $project): View
    {
        $this->authorize('view', $project);

        $project->load(['lga', 'milestones.responsibleOfficer', 'expenditures.recordedBy', 'documents.uploader']);
        $threshold = (float) SystemSetting::getValue('budget_variance_threshold_percent', 15);
        $officers = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('projects.show', compact('project', 'threshold', 'officers'));
    }

    public function edit(Project $project): View
    {
        $this->authorize('update', $project);

        return view('projects.edit', [
            'project' => $project,
            'lgas' => Lga::active()->get(),
            'statuses' => ProjectStatus::cases(),
        ]);
    }

    public function update(SaveProjectRequest $request, Project $project): RedirectResponse
    {
        $project->update($request->validated() + [
            'updated_by' => $request->user()->getKey(),
        ]);

        return redirect()->route('projects.show', $project)->with('status', "Project '{$project->title}' updated.");
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $title = $project->title;
        $project->delete();

        return redirect()->route('projects.index')->with('status', "Project '{$title}' deleted.");
    }

    public function storeMilestone(StoreMilestoneRequest $request, Project $project): RedirectResponse
    {
        $project->milestones()->create($request->validated());

        return back()->with('status', 'Project milestone added.');
    }

    public function completeMilestone(Request $request, Project $project, Milestone $milestone): RedirectResponse
    {
        $this->authorize('update', $project);

        if ($milestone->project_id !== $project->id) {
            abort(404);
        }

        $milestone->update(['completed_at' => $milestone->completed_at ? null : now()]);

        return back()->with('status', $milestone->completed_at ? 'Milestone marked completed.' : 'Milestone reopened.');
    }

    public function storeExpenditure(StoreProjectExpenditureRequest $request, Project $project): RedirectResponse
    {
        $project->expenditures()->create($request->validated() + [
            'recorded_by' => $request->user()->getKey(),
        ]);

        return back()->with('status', 'Budget & expenditure record saved.');
    }
}
