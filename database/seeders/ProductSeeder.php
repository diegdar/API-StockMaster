<?php
declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use Database\Seeders\Traits\DisablesForeignKeyChecking;

class ProductSeeder extends Seeder
{
    use DisablesForeignKeyChecking;

    public function run(): void
    {
        $this->disableForeignKeyChecking();

        Product::truncate();

        $this->enableForeignKeyChecking();

        $categories = Category::all();
        $suppliers = Supplier::all();

        if ($categories->isEmpty() || $suppliers->isEmpty()) {
            return;
        }
        Product::create([
            'name' => 'Tornillo 25mm',
            'sku' => 'PROD-NEW-001',
            'description' => 'A new product',
            'unit_price' => 149.99,
            'unit_cost' => 75.00,
            'category_id' => 1,
            'supplier_id' => 1,
            'valuation_strategy' => 'fifo',
            'min_stock_level' => 10,
        ]);

        Product::factory(20)
            ->recycle($categories)
            ->recycle($suppliers)
            ->create();
    }
}
