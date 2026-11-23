<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intangible_heritages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 180);
            $table->foreignId('lga_id')->nullable()->constrained('lgas')->nullOnDelete();
            $table->string('festival_season', 120);
            $table->text('participating_communities');
            $table->longText('historical_description');
            $table->string('safeguarding_status', 40);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['name', 'lga_id'], 'uq_intangible_name_lga');
            $table->index(['safeguarding_status', 'lga_id'], 'idx_intangible_status_lga');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intangible_heritages');
    }
};
