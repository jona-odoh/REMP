<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lgas', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('code', 20)->unique();
            $table->enum('senatorial_district', ['Kogi Central', 'Kogi East', 'Kogi West']);
            $table->string('headquarters', 100)->nullable();
            $table->decimal('centroid_lat', 10, 7)->nullable();
            $table->decimal('centroid_lng', 10, 7)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['senatorial_district', 'is_active'], 'idx_lgas_district_active');
        });

        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120)->unique();
            $table->string('slug', 120)->unique();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('license_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120)->unique();
            $table->string('code', 30)->unique();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('validity_months')->default(12);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('fee_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_type_id')->constrained('license_types')->restrictOnDelete();
            $table->string('classification_rating', 60);
            $table->decimal('amount', 12, 2);
            $table->char('currency', 3)->default('NGN');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['license_type_id', 'is_active'], 'idx_fee_license_active');
        });

        Schema::create('inspection_checklist_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_type_id')->nullable()->constrained('license_types')->nullOnDelete();
            $table->string('section', 100);
            $table->string('item_text', 255);
            $table->boolean('is_mandatory')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['license_type_id', 'is_active'], 'idx_checklist_license_active');
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->json('value');
            $table->string('description', 255)->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('inspection_checklist_templates');
        Schema::dropIfExists('fee_schedules');
        Schema::dropIfExists('license_types');
        Schema::dropIfExists('asset_categories');
        Schema::dropIfExists('lgas');
    }
};
