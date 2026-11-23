<?php

namespace App\Http\Controllers;

use App\Http\Requests\Notes\StoreNoteRequest;
use App\Models\CulturalAsset;
use App\Models\Hotel;
use App\Models\LicenseApplication;
use App\Models\Note;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;

class NoteController extends Controller
{
    public function store(StoreNoteRequest $request): RedirectResponse
    {
        $target = match ($request->string('notable_type')->toString()) {
            'cultural_asset' => CulturalAsset::findOrFail($request->integer('notable_id')),
            'hotel' => Hotel::findOrFail($request->integer('notable_id')),
            'license_application' => LicenseApplication::findOrFail($request->integer('notable_id')),
            'project' => Project::findOrFail($request->integer('notable_id')),
        };

        $target->notes()->create([
            'parent_id' => $request->validated('parent_id'),
            'body' => $request->validated('body'),
            'created_by' => $request->user()->getKey(),
        ]);

        return back()->with('status', 'Internal note posted.');
    }

    public function destroy(Note $note): RedirectResponse
    {
        $this->authorize('delete', $note);

        $note->delete();

        return back()->with('status', 'Internal note removed.');
    }
}
