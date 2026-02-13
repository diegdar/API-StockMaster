<?php
declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Database\Seeders\Traits\DisablesForeignKeyChecking;

class UserSeeder extends Seeder
{
    use DisablesForeignKeyChecking;

    public function run(): void
    {
        $this->disableForeignKeyChecking();

        User::truncate();

        $this->enableForeignKeyChecking();

        User::create([
            'name' => 'Admin User',
            'email' => 'admin@stockmaster.com',
        ])->assignRole('Admin');
        
        User::factory(10)->create()->each(function (User $user) {
            $user->assignRole('Worker');
        });        

        User::factory(10)->create()->each(function (User $user) {
            $user->assignRole('Viewer');
        });        
    }
}
