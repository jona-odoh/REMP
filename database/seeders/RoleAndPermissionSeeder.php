<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Phase 1: Foundation
            'users.view', 'users.manage',
            'audit.view',
            'config.view', 'config.manage',

            // Phase 2: Cultural & Tourism Registry
            'assets.view', 'assets.create', 'assets.update', 'assets.delete', 'assets.approve',
            'heritage.view', 'heritage.create', 'heritage.update', 'heritage.delete', 'heritage.approve',

            // Phase 3: Hotel & Hospitality Licensing
            'hotels.view', 'hotels.create', 'hotels.update', 'hotels.delete',
            'licenses.view', 'licenses.create', 'licenses.update', 'licenses.review', 'licenses.approve',
            'inspections.view', 'inspections.create', 'inspections.update', 'inspections.delete',
            'fees.view', 'fees.create', 'fees.update', 'fees.delete',

            // Phase 4: Projects & Programs
            'projects.view', 'projects.create', 'projects.update', 'projects.delete', 'projects.approve',

            // Phase 5: Reporting & KPIs
            'reports.view', 'reports.export',

            // Phase 6: Documents, Tasks & Notes
            'documents.view', 'documents.create', 'documents.update', 'documents.delete',
            'tasks.view', 'tasks.create', 'tasks.update', 'tasks.delete',
            'notes.view', 'notes.create', 'notes.update', 'notes.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $rolePermissions = [
            RoleName::SUPER_ADMIN->value => $permissions,

            RoleName::DIRECTOR->value => [
                'users.view', 'audit.view', 'config.view',
                'assets.view', 'assets.approve',
                'heritage.view', 'heritage.approve',
                'hotels.view', 'licenses.view', 'licenses.approve', 'inspections.view', 'fees.view',
                'projects.view', 'projects.approve',
                'reports.view', 'reports.export',
                'documents.view',
                'tasks.view', 'tasks.create', 'tasks.update', 'tasks.delete',
                'notes.view', 'notes.create', 'notes.update', 'notes.delete',
            ],

            RoleName::DEPT_HEAD_CULTURE->value => [
                'config.view',
                'assets.view', 'assets.create', 'assets.update', 'assets.delete', 'assets.approve',
                'heritage.view', 'heritage.create', 'heritage.update', 'heritage.delete', 'heritage.approve',
                'hotels.view', 'licenses.view', 'inspections.view', 'fees.view', 'projects.view',
                'reports.view', 'reports.export',
                'documents.view', 'documents.create', 'documents.update',
                'tasks.view', 'tasks.create', 'tasks.update', 'tasks.delete',
                'notes.view', 'notes.create', 'notes.update', 'notes.delete',
            ],

            RoleName::DEPT_HEAD_TOURISM->value => [
                'config.view',
                'assets.view', 'assets.create', 'assets.update', 'assets.delete', 'assets.approve',
                'heritage.view',
                'hotels.view', 'licenses.view', 'inspections.view', 'fees.view',
                'projects.view', 'projects.create', 'projects.update', 'projects.delete', 'projects.approve',
                'reports.view', 'reports.export',
                'documents.view', 'documents.create', 'documents.update',
                'tasks.view', 'tasks.create', 'tasks.update', 'tasks.delete',
                'notes.view', 'notes.create', 'notes.update', 'notes.delete',
            ],

            RoleName::DEPT_HEAD_HOTELS->value => [
                'config.view',
                'assets.view', 'heritage.view', 'projects.view',
                'hotels.view', 'hotels.create', 'hotels.update', 'hotels.delete',
                'licenses.view', 'licenses.create', 'licenses.update', 'licenses.review', 'licenses.approve',
                'inspections.view', 'inspections.create', 'inspections.update', 'inspections.delete',
                'fees.view', 'fees.create', 'fees.update', 'fees.delete',
                'reports.view', 'reports.export',
                'documents.view', 'documents.create', 'documents.update',
                'tasks.view', 'tasks.create', 'tasks.update', 'tasks.delete',
                'notes.view', 'notes.create', 'notes.update', 'notes.delete',
            ],

            RoleName::LICENSING_OFFICER->value => [
                'config.view',
                'assets.view', 'heritage.view', 'projects.view',
                'hotels.view', 'hotels.create', 'hotels.update',
                'licenses.view', 'licenses.create', 'licenses.update', 'licenses.review',
                'inspections.view', 'inspections.create', 'inspections.update', 'inspections.delete',
                'fees.view', 'fees.create', 'fees.update',
                'reports.view',
                'documents.view', 'documents.create', 'documents.update',
                'tasks.view', 'tasks.create', 'tasks.update',
                'notes.view', 'notes.create', 'notes.update',
            ],

            RoleName::DATA_ENTRY->value => [
                'config.view',
                'assets.view', 'assets.create', 'assets.update',
                'heritage.view', 'heritage.create', 'heritage.update',
                'hotels.view', 'hotels.create', 'hotels.update',
                'licenses.view', 'licenses.create',
                'inspections.view', 'fees.view',
                'projects.view', 'projects.create', 'projects.update',
                'reports.view',
                'documents.view', 'documents.create',
                'tasks.view', 'tasks.create', 'tasks.update',
                'notes.view', 'notes.create',
            ],

            RoleName::VIEWER->value => [
                'config.view',
                'assets.view', 'heritage.view',
                'hotels.view', 'licenses.view', 'inspections.view', 'fees.view',
                'projects.view', 'reports.view', 'documents.view', 'tasks.view', 'notes.view',
            ],
        ];

        foreach ($rolePermissions as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($perms);
        }
    }
}
