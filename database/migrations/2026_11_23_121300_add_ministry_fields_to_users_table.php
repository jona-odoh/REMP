<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('staff_id', 50)->nullable()->unique()->after('email');
            $table->string('phone', 30)->nullable()->after('staff_id');
            $table->enum('department', [
                'ADMINISTRATION',
                'CULTURE',
                'TOURISM',
                'HOTELS_HOSPITALITY',
                'PLANNING_FINANCE',
            ])->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->after('password');
            $table->boolean('must_change_password')->default(false)->after('is_active');
            $table->timestamp('deactivated_at')->nullable()->after('must_change_password');
            $table->foreignId('deactivated_by')->nullable()->after('deactivated_at')->constrained('users')->nullOnDelete();
            $table->timestamp('last_login_at')->nullable()->after('deactivated_by');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');

            $table->index(['is_active', 'department'], 'idx_users_active_dept');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['deactivated_by']);
            $table->dropIndex('idx_users_active_dept');
            $table->dropColumn([
                'staff_id',
                'phone',
                'department',
                'is_active',
                'must_change_password',
                'deactivated_at',
                'deactivated_by',
                'last_login_at',
                'last_login_ip',
            ]);
        });
    }
};
