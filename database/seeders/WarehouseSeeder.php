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

        // Hardcoded base warehouses for consistent data
        Warehouse::create(['name' => 'Almacén Central (Madrid)', 'location' => 'Polígono Industrial Vallecas, Calle Principal 100, Madrid', 'capacity' => 50000, 'is_active' => true]);
        Warehouse::create(['name' => 'Almacén Norte (Bilbao)', 'location' => 'Zona Portuaria Bilbao, Muelle 15, Bilbao', 'capacity' => 35000, 'is_active' => true]);
        Warehouse::create(['name' => 'Almacén Sur (Sevilla)', 'location' => 'Parque Logístico Sevilla, Nave 8, Sevilla', 'capacity' => 40000, 'is_active' => true]);

        // Additional random warehouses using factory
        Warehouse::factory()->count(1)->create();
    }
}
