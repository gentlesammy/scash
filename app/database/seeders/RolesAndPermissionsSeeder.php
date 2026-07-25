<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create Granular Permissions
        $permissions = [
            'reports.view_queue', 'reports.approve', 'reports.reject', 'reports.quarantine', 'reports.merge', 'reports.edit_metadata',
            'users.suspend_temporary', 'users.ban_permanent', 'users.unban', 'users.edit_credibility_score',
            'categories.manage', 'appeals.resolve', 'system.view_unredacted_evidence'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 2. Create Roles & Sync Permissions
        
        // Tier 1 Moderator
        $tier1Mod = Role::firstOrCreate(['name' => 'tier1_moderator']);
        $tier1Mod->givePermissionTo(['reports.view_queue', 'reports.approve', 'reports.reject']);

        // Tier 2 Moderator
        $tier2Mod = Role::firstOrCreate(['name' => 'tier2_moderator']);
        $tier2Mod->givePermissionTo(array_merge($tier1Mod->permissions->pluck('name')->toArray(), [
            'users.suspend_temporary', 'reports.quarantine', 'appeals.resolve'
        ]));

        // Data Integrity Admin
        $dataAdmin = Role::firstOrCreate(['name' => 'data_integrity_admin']);
        $dataAdmin->givePermissionTo(['reports.merge', 'categories.manage', 'reports.edit_metadata']);

        // Community Trust Admin
        $trustAdmin = Role::firstOrCreate(['name' => 'community_trust_admin']);
        $trustAdmin->givePermissionTo(['users.edit_credibility_score', 'users.ban_permanent', 'users.unban']);

        // Superadmin
        $superAdmin = Role::firstOrCreate(['name' => 'superadmin']);
        $superAdmin->givePermissionTo(Permission::all());
    }
}
