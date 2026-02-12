<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        // Create 1 Admin User
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@stockmaster.com',
        ]);

        // Create 5 Categories
        $categories = Category::factory(5)->create();

        // Create 3 Suppliers
        $suppliers = Supplier::factory(3)->create();

        // Create 3 Warehouses
        $warehouses = Warehouse::factory(3)->create();

        // Create 20 Products linked to random categories and suppliers
        $products = Product::factory(20)
            ->recycle($categories)
            ->recycle($suppliers)
            ->create();

        // Seed Inventory for each product in each warehouse
        foreach ($products as $product) {
            foreach ($warehouses as $warehouse) {
                // 70% chance of having stock
                if (rand(1, 100) <= 70) {
                    Inventory::factory()->create([
                        'product_id' => $product->id,
                        'warehouse_id' => $warehouse->id,
                    ]);
                }
            }
        }
    }
}
