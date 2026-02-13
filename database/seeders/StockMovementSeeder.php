<?php
declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StockMovement;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\User;
use Database\Seeders\Traits\DisablesForeignKeyChecking;
use Illuminate\Support\Collection;

class StockMovementSeeder extends Seeder
{
    use DisablesForeignKeyChecking;

    public function run(): void
    {
        $this->disableForeignKeyChecking();

        StockMovement::truncate();

        $this->enableForeignKeyChecking();

        $products = Product::all();
        $warehouses = Warehouse::all();
        $users = User::all();

        if ($products->isEmpty() || $warehouses->isEmpty() || $users->isEmpty()) {
            return;
        }

        foreach ($products as $product) {
            foreach ($warehouses as $warehouse) {
                $this->createMovementsForProductWarehouse(
                    $product,
                    $warehouse,
                    $users
                );
            }
        }
    }

    /**
     * Create realistic movements for a product-warehouse combination.
     */
    private function createMovementsForProductWarehouse(
        Product $product,
        Warehouse $warehouse,
        Collection $users
    ): void {
        $user = $users->random();

        // Create 1-3 "IN" movements first to establish stock
        $inMovements = mt_rand(1, 3);
        for ($i = 0; $i < $inMovements; $i++) {
            $quantity = mt_rand(20, 100);
            StockMovement::factory()->create([
                'type' => 'in',
                'quantity' => $quantity,
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'user_id' => $user->id,
            ]);
        }

        // Create 0-2 "OUT" movements with realistic quantities
        // (not exceeding what was created in IN movements)
        $outMovements = mt_rand(0, 2);
        if ($outMovements > 0) {
            for ($i = 0; $i < $outMovements; $i++) {
                $quantity = mt_rand(5, 30);
                StockMovement::factory()->create([
                    'type' => 'out',
                    'quantity' => $quantity,
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'user_id' => $user->id,
                ]);
            }
        }
    }
}
