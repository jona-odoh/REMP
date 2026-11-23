<?php

namespace App\Models;

use App\Enums\DocumentFileType;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Document extends Model
{
    use Auditable;

    protected $fillable = [
        'documentable_type',
        'documentable_id',
        'title',
        'description',
        'category',
        'file_type',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size',
        'document_date',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'file_type' => DocumentFileType::class,
            'file_size' => 'integer',
            'document_date' => 'date',
        ];
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function formattedSize(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2).' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return $bytes.' B';
    }
}
