<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Seed the 4 platform roles.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'Super Admin', 'slug' => 'superadmin'],
            ['name' => 'Admin',       'slug' => 'admin'],
            ['name' => 'Moderator',   'slug' => 'moderator'],
            ['name' => 'User',        'slug' => 'user'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['slug' => $role['slug']], $role);
        }
    }
}
