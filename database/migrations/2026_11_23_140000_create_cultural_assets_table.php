<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        Schema::create('cultural_assets', function (Blueprint $table) use ($driver) {
            $table->id();
            $table->string('name', 180);
            $table->foreignId('category_id')->constrained('asset_categories')->restrictOnDelete();
            $table->foreignId('lga_id')->constrained('lgas')->restrictOnDelete();
            if ($driver !== 'mysql') {
                $table->string('location', 100);
            }
            $table->boolean('is_approximate_location')->default(false);
            $table->string('ownership_status', 40);
            $table->string('condition_assessment', 30);
            $table->unsignedInteger('visitor_capacity')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('current_version')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['name', 'lga_id'], 'uq_cultural_assets_name_lga');
            $table->index(['category_id', 'lga_id', 'condition_assessment'], 'idx_assets_filter');
        });

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE cultural_assets ADD COLUMN location POINT NOT NULL SRID 4326 AFTER lga_id');
            DB::statement('CREATE SPATIAL INDEX idx_cultural_assets_location ON cultural_assets (location)');
        }

        Schema::create('cultural_asset_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cultural_asset_id')->constrained('cultural_assets')->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->json('snapshot');
            $table->string('change_summary', 255)->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['cultural_asset_id', 'version_number'], 'uq_asset_version');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cultural_asset_versions');
        Schema::dropIfExists('cultural_assets');
    }
};
