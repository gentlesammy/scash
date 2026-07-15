<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Services\PseudonymGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperadminSeeder extends Seeder
{
    /**
     * Seed the default superadmin account.
     */
    public function run(): void
    {
        $superadminRole = Role::where('slug', 'superadmin')->first();

        if (!$superadminRole) {
            $this->command->error('Roles must be seeded first. Run RoleSeeder.');
            return;
        }

        User::firstOrCreate(
            ['email' => 'admin@scash.com.ng'],
            [
                'pseudonym'         => 'SystemAdmin_001',
                'phone'             => '+2340000000001',
                'email'             => 'admin@scash.com.ng',
                'password'          => Hash::make('ScashAdmin@2026!'),
                'role_id'           => $superadminRole->id,
                'trust_points'      => 1000,
                'credibility_rank'  => 10,
                'phone_verified_at' => now(),
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Superadmin seeded: admin@scash.com.ng');
    }
}
