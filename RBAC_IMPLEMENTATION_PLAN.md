# RBAC Architectural Implementation Plan

**Objective:** Implement a secure, scalable, and granular Role-Based Access Control (RBAC) system for Admins and Moderators on the SCASH platform.
**Target Audience:** Junior Developers / Junior AI Agents
**Architecture Strategy:** Utilize the industry-standard `spatie/laravel-permission` package for robust cache-optimized database-backed roles, combined with a custom Audit Log system for non-repudiation and security.

---

## Phase 1: Package Installation & Configuration

To avoid reinventing the wheel and to ensure maximum security, we will use Spatie's permission package. It caches role checks automatically, ensuring zero database overhead on page loads.

### Steps to Implement:
1. **Install Package:** 
   Run `composer require spatie/laravel-permission`
2. **Publish Configuration and Migrations:** 
   Run `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`
3. **Migrate Database:** 
   Run `php artisan migrate` to create the `roles`, `permissions`, `model_has_roles`, etc., tables.

---

## Phase 2: User Model Preparation

We need to attach the Spatie traits to the User model so we can easily call `$user->assignRole('admin')` or `$user->hasPermissionTo('reports.merge')`.

### Steps to Implement:
1. Open `app/Models/User.php`.
2. Import the trait: `use Spatie\Permission\Traits\HasRoles;`
3. Add `HasRoles` inside the class definition:
   ```php
   class User extends Authenticatable {
       use HasFactory, Notifiable, HasRoles;
       // ... existing code ...
   }
   ```

---

## Phase 3: Define Roles and Permissions via Seeder

We must programmatically define the exact permissions and roles so they can be securely deployed across environments (local, staging, production) without manual database entry.

### Steps to Implement:
1. Generate a seeder: `php artisan make:seeder RolesAndPermissionsSeeder`
2. In the `run()` method of the seeder, first clear Spatie's cache:
   ```php
   app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
   ```
3. **Create Granular Permissions:**
   ```php
   $permissions = [
       'reports.view_queue', 'reports.approve', 'reports.reject', 'reports.quarantine', 'reports.merge', 'reports.edit_metadata',
       'users.suspend_temporary', 'users.ban_permanent', 'users.unban', 'users.edit_credibility_score',
       'categories.manage', 'appeals.resolve', 'system.view_unredacted_evidence'
   ];
   foreach ($permissions as $permission) {
       Permission::create(['name' => $permission]);
   }
   ```
4. **Create Roles & Sync Permissions:**
   ```php
   // Tier 1 Moderator
   $tier1Mod = Role::create(['name' => 'tier1_moderator']);
   $tier1Mod->givePermissionTo(['reports.view_queue', 'reports.approve', 'reports.reject']);

   // Tier 2 Moderator
   $tier2Mod = Role::create(['name' => 'tier2_moderator']);
   $tier2Mod->givePermissionTo(array_merge($tier1Mod->permissions->pluck('name')->toArray(), [
       'users.suspend_temporary', 'reports.quarantine', 'appeals.resolve'
   ]));

   // Data Integrity Admin
   $dataAdmin = Role::create(['name' => 'data_integrity_admin']);
   $dataAdmin->givePermissionTo(['reports.merge', 'categories.manage', 'reports.edit_metadata']);

   // Community Trust Admin
   $trustAdmin = Role::create(['name' => 'community_trust_admin']);
   $trustAdmin->givePermissionTo(['users.edit_credibility_score', 'users.ban_permanent', 'users.unban']);

   // Superadmin (Implicitly gets all via Gate in AuthServiceProvider, but we can assign all just in case)
   $superAdmin = Role::create(['name' => 'superadmin']);
   $superAdmin->givePermissionTo(Permission::all());
   ```

---

## Phase 4: Security Audit Logging (Critical)

We cannot trust Moderators implicitly. If a Mod goes rogue and bans innocent users, the Superadmin must be able to trace and revert it.

### Steps to Implement:
1. Generate Model and Migration: `php artisan make:model AuditLog -m`
2. Define the schema in the migration:
   ```php
   $table->id();
   $table->foreignId('user_id')->constrained(); // The admin/mod who took the action
   $table->string('action'); // e.g., 'merged_report', 'banned_user'
   $table->string('target_type'); // e.g., 'App\Models\Report'
   $table->unsignedBigInteger('target_id'); 
   $table->json('old_values')->nullable(); // Snapshot of data before change
   $table->json('new_values')->nullable(); // Snapshot of data after change
   $table->ipAddress('ip_address');
   $table->timestamps();
   ```
3. **Implementation Rule for Devs:** Whenever a junior dev writes a Livewire method that performs a Mod/Admin action (e.g., `banUser()`), they MUST insert a record into the `AuditLog` table wrapping the logic.

---

## Phase 5: Route and Component Protection (Middleware & Gates)

We must secure both backend API endpoints/routes and frontend UI components.

### Steps to Implement:
1. **Route Protection (routes/web.php):**
   Group the admin routes using Spatie's middleware. (In Laravel 11, register the aliases in `bootstrap/app.php`).
   ```php
   Route::middleware(['auth', 'permission:reports.view_queue'])->group(function () {
       Route::get('/admin/queue', \App\Livewire\Admin\ReportQueue::class);
   });
   ```
2. **Controller/Livewire Protection:**
   At the beginning of any destructive Livewire method (e.g., `mergeReports()`), explicitly check authorization:
   ```php
   if (!auth()->user()->can('reports.merge')) {
       abort(403, 'Unauthorized action.');
   }
   ```
3. **Frontend UI Protection (Blade):**
   Use the `@can` directive to completely hide buttons from unauthorized roles so they aren't even tempted to click them.
   ```blade
   @can('users.ban_permanent')
       <button wire:click="banUser({{ $user->id }})" class="btn btn-danger">Permanently Ban</button>
   @endcan
   ```

---

## Phase 6: Global Superadmin Bypass (Failsafe)

To prevent locking the Superadmin out if permissions get mangled, we configure Laravel's global Gate.

### Steps to Implement:
1. Open `app/Providers/AppServiceProvider.php`.
2. Inside the `boot()` method, add the Gate `before` callback:
   ```php
   use Illuminate\Support\Facades\Gate;

   public function boot(): void
   {
       // Implicitly grant "Superadmin" role all permissions
       Gate::before(function ($user, $ability) {
           return $user->hasRole('superadmin') ? true : null;
       });
   }
   ```

---

### End of Implementation Plan
**Note to Implementer:** Execute Phase 1 first. Then implement Phase 2 and 3 and run `php artisan db:seed --class=RolesAndPermissionsSeeder` before building out the Livewire dashboards (Phases 4-6).
