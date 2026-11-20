<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Geographic & Community Management
        Schema::create('lgas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->timestamps();
        });

        Schema::create('wards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lga_id')->constrained('lgas')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('communities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ward_id')->constrained('wards')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('estimated_population')->nullable();
            $table->integer('estimated_households')->nullable();
            $table->enum('electricity_status_enum', ['no_electricity', 'unreliable', 'inadequate', 'reliable'])->default('no_electricity');
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
        });

        // 2. Public Reporting
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->foreignId('lga_id')->constrained('lgas')->cascadeOnDelete();
            $table->foreignId('ward_id')->constrained('wards')->cascadeOnDelete();
            $table->foreignId('community_id')->nullable()->constrained('communities')->nullOnDelete();
            $table->string('custom_community_name')->nullable();
            $table->string('reporter_name')->nullable();
            $table->string('reporter_contact')->nullable();
            $table->enum('electricity_situation_enum', ['no_electricity', 'irregular', 'inadequate', 'partial']);
            $table->string('duration_of_problem')->nullable();
            $table->integer('affected_households_estimate')->nullable();
            $table->json('affected_facilities_json')->nullable();
            $table->text('description')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('photo_path')->nullable();
            $table->enum('status_enum', ['submitted', 'under_review', 'verified', 'rejected', 'assessed'])->default('submitted');
            $table->boolean('is_potential_duplicate')->default(false);
            $table->text('verification_comments')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('assigned_officer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 3. Field Assessment (Energy Needs)
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->constrained('communities')->cascadeOnDelete();
            $table->foreignId('officer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('assessment_date');
            $table->boolean('grid_connected_boolean')->default(false);
            $table->decimal('average_supply_hours', 5, 2)->default(0);
            $table->string('interruption_frequency')->nullable();
            $table->string('alternative_energy_sources')->nullable();
            $table->integer('households_with_electricity')->default(0);
            $table->integer('households_without_electricity')->default(0);
            $table->string('gps_coordinates')->nullable();
            $table->string('photo_path')->nullable();
            $table->enum('status_enum', ['draft', 'completed'])->default('draft');
            $table->timestamps();
        });

        Schema::create('assessment_households', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
            $table->integer('households_assessed_count')->default(0);
            $table->integer('households_without_electricity')->default(0);
            $table->string('existing_sources')->nullable();
            $table->json('common_appliances_json')->nullable();
            $table->decimal('estimated_daily_usage_kwh', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('assessment_schools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
            $table->string('name');
            $table->string('location')->nullable();
            $table->string('school_type')->nullable();
            $table->integer('classrooms_count')->default(0);
            $table->integer('offices_count')->default(0);
            $table->json('equipment_appliances_json')->nullable();
            $table->string('existing_sources')->nullable();
            $table->decimal('operating_hours', 5, 2)->default(8);
            $table->timestamps();
        });

        Schema::create('assessment_health_facilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
            $table->string('name');
            $table->string('location')->nullable();
            $table->string('facility_type')->nullable();
            $table->json('medical_equipment_json')->nullable();
            $table->string('refrigeration_requirements')->nullable();
            $table->string('lighting_needs')->nullable();
            $table->string('existing_sources')->nullable();
            $table->timestamps();
        });

        Schema::create('assessment_businesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
            $table->string('name');
            $table->string('category')->nullable();
            $table->string('business_type')->nullable();
            $table->json('equipment_appliances_json')->nullable();
            $table->decimal('operating_hours', 5, 2)->default(8);
            $table->decimal('estimated_requirement_kwh', 10, 2)->default(0);
            $table->string('existing_sources')->nullable();
            $table->timestamps();
        });

        // 4. Energy Calculation & Recommendations
        Schema::create('appliances', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('rated_power_watts');
            $table->string('category')->default('general');
            $table->timestamps();
        });

        Schema::create('assessment_appliances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
            $table->enum('facility_type', ['household', 'school', 'hospital', 'business']);
            $table->unsignedBigInteger('facility_record_id')->nullable();
            $table->foreignId('appliance_id')->nullable()->constrained('appliances')->nullOnDelete();
            $table->string('custom_appliance_name')->nullable();
            $table->integer('rated_power_watts')->default(0);
            $table->integer('quantity')->default(1);
            $table->decimal('operating_hours_per_day', 5, 2)->default(1);
            $table->decimal('calculated_daily_energy_wh', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('energy_demands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->constrained('communities')->cascadeOnDelete();
            $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
            $table->decimal('total_daily_kwh', 12, 2)->default(0);
            $table->decimal('peak_load_kw', 10, 2)->default(0);
            $table->json('calculation_assumptions_json')->nullable();
            $table->foreignId('calculated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->constrained('communities')->cascadeOnDelete();
            $table->foreignId('energy_demand_id')->constrained('energy_demands')->cascadeOnDelete();
            $table->enum('recommended_solution_enum', [
                'solar_home_system',
                'solar_mini_grid',
                'institutional_solar',
                'solar_water_pumping',
                'productive_use_solar'
            ]);
            $table->text('justification')->nullable();
            $table->decimal('estimated_capacity_kw', 10, 2)->default(0);
            $table->json('equipment_requirements_json')->nullable();
            $table->text('analyst_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('cost_estimates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recommendation_id')->constrained('recommendations')->cascadeOnDelete();
            $table->decimal('solar_panels_cost', 15, 2)->default(0);
            $table->decimal('batteries_cost', 15, 2)->default(0);
            $table->decimal('inverters_cost', 15, 2)->default(0);
            $table->decimal('charge_controllers_cost', 15, 2)->default(0);
            $table->decimal('mounting_structures_cost', 15, 2)->default(0);
            $table->decimal('distribution_cost', 15, 2)->default(0);
            $table->decimal('protection_equipment_cost', 15, 2)->default(0);
            $table->decimal('installation_cost', 15, 2)->default(0);
            $table->decimal('transportation_cost', 15, 2)->default(0);
            $table->decimal('total_estimated_cost_ngn', 16, 2)->default(0);
            $table->json('assumptions_json')->nullable();
            $table->timestamps();
        });

        // 5. Prioritisation
        Schema::create('priority_rankings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->constrained('communities')->cascadeOnDelete();
            $table->decimal('score', 6, 2)->default(0);
            $table->enum('priority_level_enum', ['high', 'medium', 'low'])->default('low');
            $table->json('ranking_criteria_json')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('priority_rankings');
        Schema::dropIfExists('cost_estimates');
        Schema::dropIfExists('recommendations');
        Schema::dropIfExists('energy_demands');
        Schema::dropIfExists('assessment_appliances');
        Schema::dropIfExists('appliances');
        Schema::dropIfExists('assessment_businesses');
        Schema::dropIfExists('assessment_health_facilities');
        Schema::dropIfExists('assessment_schools');
        Schema::dropIfExists('assessment_households');
        Schema::dropIfExists('assessments');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('communities');
        Schema::dropIfExists('wards');
        Schema::dropIfExists('lgas');
    }
};
