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

        Schema::create('hotels', function (Blueprint $table) use ($driver) {
            $table->id();
            $table->string('name', 180);
            $table->string('address', 255);
            $table->foreignId('lga_id')->constrained('lgas')->restrictOnDelete();
            $table->string('establishment_type', 60)->default('Hotel');
            $table->unsignedInteger('capacity_rooms')->default(0);
            $table->unsignedInteger('capacity_beds')->default(0);
            $table->string('owner_name', 150);
            $table->string('owner_contact', 100);
            $table->string('classification_rating', 60);
            $table->string('license_status', 30)->default('UNLICENSED');
            if ($driver !== 'mysql') {
                $table->string('location', 100);
            }
            $table->boolean('is_approximate_location')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['name', 'lga_id'], 'uq_hotels_name_lga');
            $table->index(['lga_id', 'license_status', 'establishment_type'], 'idx_hotels_compliance');
        });

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE hotels ADD COLUMN location POINT NOT NULL SRID 4326 AFTER license_status');
            DB::statement('CREATE SPATIAL INDEX idx_hotels_location ON hotels (location)');
        }

        Schema::create('license_applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_number', 40)->unique();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->foreignId('license_type_id')->constrained('license_types')->restrictOnDelete();
            $table->enum('application_type', ['NEW', 'RENEWAL'])->default('NEW');
            $table->string('status', 30)->default('DRAFT');
            $table->foreignId('assigned_officer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('decision_reason')->nullable();
            $table->string('license_number', 50)->nullable()->unique();
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'assigned_officer_id'], 'idx_license_queue');
            $table->index(['status', 'expires_at'], 'idx_license_expiry');
        });

        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->foreignId('license_application_id')->nullable()->constrained('license_applications')->nullOnDelete();
            $table->foreignId('assigned_officer_id')->constrained('users')->restrictOnDelete();
            $table->date('scheduled_date');
            $table->date('completed_date')->nullable();
            $table->string('status', 30)->default('SCHEDULED');
            $table->json('checklist_results')->nullable();
            $table->boolean('overall_passed')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'scheduled_date'], 'idx_inspections_overdue');
            $table->index(['hotel_id', 'status'], 'idx_inspections_hotel');
        });

        Schema::create('license_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->foreignId('license_application_id')->nullable()->constrained('license_applications')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('receipt_reference', 80)->unique();
            $table->date('paid_at');
            $table->string('period', 60);
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['paid_at', 'hotel_id'], 'idx_fees_paid_hotel');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_fees');
        Schema::dropIfExists('inspections');
        Schema::dropIfExists('license_applications');
        Schema::dropIfExists('hotels');
    }
};
