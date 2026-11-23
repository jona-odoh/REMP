<?php

namespace App\Http\Controllers;

use App\Enums\AuditAction;
use App\Enums\DocumentFileType;
use App\Http\Requests\Document\StoreDocumentRequest;
use App\Models\CulturalAsset;
use App\Models\Document;
use App\Models\Hotel;
use App\Models\Inspection;
use App\Models\IntangibleHeritage;
use App\Models\LicenseApplication;
use App\Models\Project;
use App\Services\AuditService;
use App\Services\DocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function __construct(
        private readonly DocumentService $documentService,
        private readonly AuditService $auditService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Document::class);

        $search = trim((string) $request->input('search', ''));
        $driver = DB::getDriverName();

        $documents = Document::query()
            ->with(['uploader', 'documentable'])
            ->when($search !== '', function ($query) use ($search, $driver) {
                if ($driver === 'mysql') {
                    // Uses MySQL InnoDB FULLTEXT index (idx_documents_fulltext) with LIKE fallback for short terms (<3 chars)
                    if (strlen($search) >= 3) {
                        $query->whereRaw('MATCH(title, description) AGAINST(? IN NATURAL LANGUAGE MODE)', [$search]);
                    } else {
                        $query->where(function ($sub) use ($search) {
                            $sub->where('title', 'like', "%{$search}%")
                                ->orWhere('description', 'like', "%{$search}%");
                        });
                    }
                } else {
                    $query->where(function ($sub) use ($search) {
                        $sub->where('title', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%")
                            ->orWhere('original_filename', 'like', "%{$search}%");
                    });
                }
            })
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')->toString()))
            ->when($request->filled('file_type'), fn ($q) => $q->where('file_type', $request->string('file_type')->toString()))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $categories = Document::distinct()->orderBy('category')->pluck('category');

        return view('documents.index', [
            'documents' => $documents,
            'categories' => $categories,
            'fileTypes' => DocumentFileType::cases(),
            'search' => $search,
        ]);
    }

    public function show(Request $request, Document $document): View
    {
        $this->authorize('view', $document);

        $this->auditService->record(
            action: AuditAction::VIEW,
            entity: $document,
            metadata: ['filename' => $document->original_filename],
            actor: $request->user(),
        );

        return view('documents.show', [
            'document' => $document->load(['uploader', 'documentable']),
        ]);
    }

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
                'project' => Project::findOrFail($request->integer('documentable_id')),
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
