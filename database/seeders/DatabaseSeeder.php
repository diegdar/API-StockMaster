<?php
declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Order of execution is critical for foreign key integrity:
     * 1. Roles and Permissions (independent)
     * 2. Users (independent)
     * 3. Categories, Suppliers, Warehouses (master tables)
     * 4. Products (depends on categories and suppliers)
     * 5. StockMovements (depends on products, warehouses, users)
     *    - This will trigger StockMovementObserver to populate Inventory table
     */
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            SupplierSeeder::class,
            WarehouseSeeder::class,
            ProductSeeder::class,
            StockMovementSeeder::class,
        ]);
    }
}
