<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('documentable');
            $table->string('title', 180);
            $table->text('description')->nullable();
            $table->string('category', 80)->default('General');
            $table->string('file_type', 20); // photo, video, audio, document
            $table->string('file_path', 500);
            $table->string('original_filename', 255);
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('file_size');
            $table->date('document_date')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['category', 'file_type'], 'idx_documents_cat_type');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE documents ADD FULLTEXT INDEX idx_documents_fulltext (title, description)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
