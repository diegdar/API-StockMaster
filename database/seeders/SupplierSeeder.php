<?php
declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;
use Database\Seeders\Traits\DisablesForeignKeyChecking;

class SupplierSeeder extends Seeder
{
    use DisablesForeignKeyChecking;

    public function run(): void
    {
        $this->disableForeignKeyChecking();

        Supplier::truncate();

        $this->enableForeignKeyChecking();

        Supplier::factory()->count(3)->create();
    }
}
