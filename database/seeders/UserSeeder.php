<?php
declare(strict_types=1);

namespace Database\Seeders;

use Hash;
use Illuminate\Database\Seeder;
use App\Models\User;
use Database\Seeders\Traits\DisablesForeignKeyChecking;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    use DisablesForeignKeyChecking;

    public function run(): void
    {
        $this->disableForeignKeyChecking();

        User::truncate();

        $this->enableForeignKeyChecking();

        // Get roles with API guard
        $adminRole = Role::where('name', 'Admin')->where('guard_name', 'api')->first();
        $workerRole = Role::where('name', 'Worker')->where('guard_name', 'api')->first();
        $viewerRole = Role::where('name', 'Viewer')->where('guard_name', 'api')->first();

        User::create([
            'name' => 'Admin User',
            'email' => 'admin@stockmaster.com',
            'password'=> Hash::make('Password$1234'),
        ])->assignRole($adminRole);

        User::create([
            'name' => 'Worker User',
            'email' => 'worker@stockmaster.com',
            'password'=> Hash::make('Password$1234'),
        ])->assignRole($workerRole);

        User::create([
            'name' => 'Viewer User',
            'email' => 'viewer@stockmaster.com',
            'password'=> Hash::make('Password$1234'),
        ])->assignRole($viewerRole);

        User::factory(10)->create()->each(function (User $user) use ($workerRole) {
            $user->assignRole($workerRole);
        });

        User::factory(10)->create()->each(function (User $user) use ($viewerRole) {
            $user->assignRole($viewerRole);
        });
    }
}
