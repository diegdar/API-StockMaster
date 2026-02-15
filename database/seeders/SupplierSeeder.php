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

        // Hardcoded base suppliers for consistent data
        Supplier::create(['name' => 'Suministros Industriales Díaz', 'contact_email' => 'contacto@diaz-suministros.es', 'phone' => '+34 900 111 001', 'address' => 'Calle Industrial 25, Madrid']);
        Supplier::create(['name' => 'Materiales Eléctricos López', 'contact_email' => 'ventas@lopez-electric.es', 'phone' => '+34 900 111 002', 'address' => 'Avenida Electricidad 50, Barcelona']);
        Supplier::create(['name' => 'Fontanería y Cerámica Martinez', 'contact_email' => 'info@martinez-fontaneria.es', 'phone' => '+34 900 111 003', 'address' => 'Plaza Agua 10, Valencia']);

        // Additional random suppliers using factory
        Supplier::factory()->count(1)->create();
    }
}
