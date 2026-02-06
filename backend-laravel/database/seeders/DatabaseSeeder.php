<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Level;
use App\Models\Position;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@company.com',
            'password' => Hash::make('4dm1nkp1'),
            'role' => 'admin',
        ]);

        $departments = [
            'Customer Service',
            'Customer Service & Delivery',
            'Delivery',
            'Design & Engineering',
            'Design & Marketing',
            'Director',
            'Executive Secretary',
            'Facility Control',
            'Finance & Accounting',
            'HR &GA',
            'Marketing',
            'Material Control',
            'Netsuite & IT',
            'Operation Manager',
            'Procurement',
            'Production',
            'Production Control',
            'QA',
            'Warehouse & Logistics',
        ];

        foreach ($departments as $departmentName) {
            Department::factory()->create([
                'name' => $departmentName,
            ]);
        }

        $levels = [
            'ADM',
            'Director',
            'DL',
            'IL',
            'Manager',
        ];

        foreach ($levels as $level) {
            Level::factory()->create([
                'level' => $level,
            ]);
        }

        $position = [
            'Admin',
            'Assistant Manager',
            'Assistant Supervisor',
            'Coordinator',
            'Director',
            'Finance & Accounting Head',
            'Leader',
            'Operation Manager',
            'Operator',
            'Senior Coordinator',
            'Senior Leader',
            'Senior Staff',
            'Senior Supervisor',
            'Staff',
            'Sub Coordinator',
            'Sub Leader',
            'Supervisor',
        ];

        foreach ($position as $positionTitle) {
            Position::factory()->create([
                'title' => $positionTitle,
            ]);
        }
    }
}
