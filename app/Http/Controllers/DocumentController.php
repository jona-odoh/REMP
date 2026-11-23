<?php

namespace App\Http\Controllers;

use App\Http\Requests\Document\StoreDocumentRequest;
use App\Models\CulturalAsset;
use App\Models\Document;
use App\Models\Hotel;
use App\Models\Inspection;
use App\Models\IntangibleHeritage;
use App\Models\LicenseApplication;
use App\Services\DocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function __construct(
        private readonly DocumentService $documentService,
    ) {}

    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $attachable = null;
        if ($request->filled('documentable_type') && $request->filled('documentable_id')) {
            $attachable = match ($request->string('documentable_type')->toString()) {
                'cultural_asset' => CulturalAsset::findOrFail($request->integer('documentable_id')),
                'intangible_heritage' => IntangibleHeritage::findOrFail($request->integer('documentable_id')),
                'hotel' => Hotel::findOrFail($request->integer('documentable_id')),
                'license_application' => LicenseApplication::findOrFail($request->integer('documentable_id')),
                'inspection' => Inspection::findOrFail($request->integer('documentable_id')),
                default => null,
            };
        }

        $document = $this->documentService->upload(
            file: $request->file('file'),
            metadata: $request->validated(),
            attachable: $attachable,
            actor: $request->user(),
        );

        return back()->with('status', "File '{$document->title}' uploaded to local Ministry storage.");
    }

    public function download(Request $request, Document $document): StreamedResponse
    {
        $this->authorize('view', $document);

        return $this->documentService->download($document, $request->user());
    }

    public function destroy(Document $document): RedirectResponse
    {
        $this->authorize('delete', $document);

        $title = $document->title;
        $this->documentService->delete($document);

        return back()->with('status', "Document '{$title}' deleted.");
    }
}
