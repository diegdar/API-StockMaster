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

        // Create Permissions
        Permission::create(['name' => 'manage products']);
        Permission::create(['name' => 'manage warehouses']);
        Permission::create(['name' => 'record movements']);
        Permission::create(['name' => 'view inventory']);

        // Create Admin Role and give all permissions
        $admin = Role::create(['name' => 'Admin']);
        $admin->givePermissionTo(Permission::all());

        // Create Worker Role
        $worker = Role::create(['name' => 'Worker']);
        $worker->givePermissionTo(['record movements', 'view inventory']);

        // Create Viewer Role
        $viewer = Role::create(['name' => 'Viewer']);
        $viewer->givePermissionTo(['view inventory']);
    }
}
