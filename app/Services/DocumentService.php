<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\DocumentFileType;
use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentService
{
    public function __construct(
        private readonly AuditService $auditService,
    ) {}

    /**
     * Store an uploaded file on the local private on-premise disk and attach it polymorphically.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function upload(UploadedFile $file, array $metadata, ?Model $attachable, User $actor): Document
    {
        $mime = (string) $file->getMimeType();
        $fileType = $this->detectFileType($mime);
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin');
        $directory = 'documents/'.now()->format('Y/m');
        $filename = Str::uuid()->toString().'.'.$extension;

        $path = $file->storeAs($directory, $filename, 'local');

        return Document::create([
            'documentable_type' => $attachable?->getMorphClass(),
            'documentable_id' => $attachable?->getKey(),
            'title' => $metadata['title'] ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'description' => $metadata['description'] ?? null,
            'category' => $metadata['category'] ?? 'General',
            'file_type' => $fileType,
            'file_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $mime,
            'file_size' => $file->getSize() ?: 0,
            'document_date' => $metadata['document_date'] ?? now()->toDateString(),
            'uploaded_by' => $actor->getKey(),
        ]);
    }

    public function download(Document $document, User $actor): StreamedResponse
    {
        $this->auditService->record(
            action: AuditAction::DOWNLOAD,
            entity: $document,
            metadata: [
                'filename' => $document->original_filename,
                'documentable_type' => $document->documentable_type,
                'documentable_id' => $document->documentable_id,
            ],
            actor: $actor,
        );

        return Storage::disk('local')->download($document->file_path, $document->original_filename);
    }

    public function delete(Document $document): void
    {
        if (Storage::disk('local')->exists($document->file_path)) {
            Storage::disk('local')->delete($document->file_path);
        }

        $document->delete();
    }

    protected function detectFileType(string $mime): DocumentFileType
    {
        return match (true) {
            str_starts_with($mime, 'image/') => DocumentFileType::PHOTO,
            str_starts_with($mime, 'video/') => DocumentFileType::VIDEO,
            str_starts_with($mime, 'audio/') => DocumentFileType::AUDIO,
            default => DocumentFileType::DOCUMENT,
        };
    }
}
