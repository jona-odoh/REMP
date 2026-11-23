<?php

namespace Database\Seeders;

use App\Enums\Department;
use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $demoUsers = [
            [
                'name' => 'Ibrahim Suleiman',
                'email' => 'admin',
                'password' => 'admin',
                'staff_id' => 'KSMCT/ADM/001',
                'phone' => '+234 803 000 1001',
                'department' => Department::ADMINISTRATION,
                'role' => RoleName::SUPER_ADMIN->value,
            ],
            [
                'name' => 'Dr. Amina Ocheja',
                'email' => 'director@ksmct.kogistate.gov.ng',
                'staff_id' => 'KSMCT/DIR/002',
                'phone' => '+234 803 000 1002',
                'department' => Department::ADMINISTRATION,
                'role' => RoleName::DIRECTOR->value,
            ],
            [
                'name' => 'Chief Samuel Olorunshola',
                'email' => 'culture.head@ksmct.kogistate.gov.ng',
                'staff_id' => 'KSMCT/CUL/003',
                'phone' => '+234 803 000 1003',
                'department' => Department::CULTURE,
                'role' => RoleName::DEPT_HEAD_CULTURE->value,
            ],
            [
                'name' => 'Hajiya Mariam Salihu',
                'email' => 'tourism.head@ksmct.kogistate.gov.ng',
                'staff_id' => 'KSMCT/TRM/004',
                'phone' => '+234 803 000 1004',
                'department' => Department::TOURISM,
                'role' => RoleName::DEPT_HEAD_TOURISM->value,
            ],
            [
                'name' => 'Engr. Joseph Adaji',
                'email' => 'hotels.head@ksmct.kogistate.gov.ng',
                'staff_id' => 'KSMCT/HTL/005',
                'phone' => '+234 803 000 1005',
                'department' => Department::HOTELS_HOSPITALITY,
                'role' => RoleName::DEPT_HEAD_HOTELS->value,
            ],
            [
                'name' => 'Grace Akubo',
                'email' => 'licensing@ksmct.kogistate.gov.ng',
                'staff_id' => 'KSMCT/HTL/006',
                'phone' => '+234 803 000 1006',
                'department' => Department::HOTELS_HOSPITALITY,
                'role' => RoleName::LICENSING_OFFICER->value,
            ],
            [
                'name' => 'Musa Abdullahi',
                'email' => 'dataentry@ksmct.kogistate.gov.ng',
                'staff_id' => 'KSMCT/PRF/007',
                'phone' => '+234 803 000 1007',
                'department' => Department::PLANNING_FINANCE,
                'role' => RoleName::DATA_ENTRY->value,
            ],
            [
                'name' => 'Victoria Enefola',
                'email' => 'viewer@ksmct.kogistate.gov.ng',
                'staff_id' => 'KSMCT/PRF/008',
                'phone' => '+234 803 000 1008',
                'department' => Department::PLANNING_FINANCE,
                'role' => RoleName::VIEWER->value,
            ],
        ];

        foreach ($demoUsers as $item) {
            $role = $item['role'];
            $password = $item['password'] ?? 'Password@2026!';
            unset($item['role'], $item['password']);

            $user = User::updateOrCreate(
                ['email' => $item['email']],
                $item + [
                    'password' => $password,
                    'is_active' => true,
                    'must_change_password' => false,
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([$role]);
        }
    }
}
