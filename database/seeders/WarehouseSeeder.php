<?php
declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Warehouse;
use Database\Seeders\Traits\DisablesForeignKeyChecking;

class WarehouseSeeder extends Seeder
{
    use DisablesForeignKeyChecking;

    public function run(): void
    {
        $this->disableForeignKeyChecking();

        Warehouse::truncate();

        $this->enableForeignKeyChecking();

        Warehouse::factory()->count(3)->create();
    }
}
