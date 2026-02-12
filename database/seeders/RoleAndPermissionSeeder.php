<?php
declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

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
