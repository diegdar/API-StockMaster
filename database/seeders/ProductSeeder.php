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

        Product::factory(20)
            ->recycle($categories)
            ->recycle($suppliers)
            ->create();
    }
}
