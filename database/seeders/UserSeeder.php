<?php
declare(strict_types=1);

namespace Database\Seeders;

use Hash;
use Illuminate\Database\Seeder;
use App\Models\User;
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
            'password'=> Hash::make('Password$123'),
        ])->assignRole('Admin');
        
        User::factory(10)->create()->each(function (User $user) {
            $user->assignRole('Worker');
        });        

        User::factory(10)->create()->each(function (User $user) {
            $user->assignRole('Viewer');
        });        
    }
}
