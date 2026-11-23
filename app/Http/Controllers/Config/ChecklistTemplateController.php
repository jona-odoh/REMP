<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Http\Requests\Config\SaveChecklistTemplateRequest;
use App\Models\InspectionChecklistTemplate;
use App\Models\LicenseType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ChecklistTemplateController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', InspectionChecklistTemplate::class);

        $templates = InspectionChecklistTemplate::with('licenseType')
            ->orderBy('section')
            ->orderBy('sort_order')
            ->paginate(25);
        $licenseTypes = LicenseType::active()->get();

        return view('config.checklist-templates.index', compact('templates', 'licenseTypes'));
    }

    public function store(SaveChecklistTemplateRequest $request): RedirectResponse
    {
        InspectionChecklistTemplate::create($request->validated() + [
            'is_mandatory' => $request->boolean('is_mandatory', true),
            'sort_order' => (int) $request->input('sort_order', 0),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('config.checklist-templates.index')->with('status', 'Checklist item added.');
    }

    public function update(SaveChecklistTemplateRequest $request, InspectionChecklistTemplate $checklistTemplate): RedirectResponse
    {
        $checklistTemplate->update($request->validated() + [
            'is_mandatory' => $request->boolean('is_mandatory', false),
            'sort_order' => (int) $request->input('sort_order', 0),
            'is_active' => $request->boolean('is_active', false),
        ]);

        return redirect()->route('config.checklist-templates.index')->with('status', 'Checklist item updated.');
    }

    public function destroy(InspectionChecklistTemplate $checklistTemplate): RedirectResponse
    {
        $this->authorize('delete', $checklistTemplate);

        $checklistTemplate->delete();

        return redirect()->route('config.checklist-templates.index')->with('status', 'Checklist item deleted.');
    }
}
