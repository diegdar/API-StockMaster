<?php
declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Database\Seeders\Traits\DisablesForeignKeyChecking;

class RoleAndPermissionSeeder extends Seeder
{
    use DisablesForeignKeyChecking;

    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->disableForeignKeyChecking();

        // Clean existing permissions and roles
        Permission::truncate();
        Role::truncate();

        $this->enableForeignKeyChecking();

        // // Create Permissions (for API guard)
        Role::create(['name' => 'Admin', 'guard_name' => 'api']);
        Role::create(['name' => 'Worker', 'guard_name' => 'api']);
        Role::create(['name' => 'Viewer', 'guard_name' => 'api']);
    }
}
