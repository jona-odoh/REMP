<?php

namespace Database\Seeders;

use App\Services\AuditService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        AuditService::$disabled = true;

        try {
            $this->call([
                RoleAndPermissionSeeder::class,
                LgaSeeder::class,
                SystemConfigSeeder::class,
                DemoUserSeeder::class,
                CulturalHeritageSeeder::class,
            ]);
        } finally {
            AuditService::$disabled = false;
        }
    }
}
