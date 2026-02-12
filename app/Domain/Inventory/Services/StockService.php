<?php
declare(strict_types=1);

namespace App\Domain\Inventory\Services;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\StockMovement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Record an incoming stock movement.
     */
    public function recordIn(Product $product, Warehouse $warehouse, int $quantity, float $unitCost, ?string $description = null): StockMovement
    {
        return DB::transaction(function () use ($product, $warehouse, $quantity, $unitCost, $description) {
            return StockMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'type' => 'in',
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'description' => $description,
                'user_id' => auth()->id(),
            ]);
        });
    }

    /**
     * Record an outgoing stock movement.
     */
    public function recordOut(Product $product, Warehouse $warehouse, int $quantity, ?string $description = null): StockMovement
    {
        return DB::transaction(function () use ($product, $warehouse, $quantity, $description) {
            return StockMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'type' => 'out',
                'quantity' => $quantity,
                'description' => $description,
                'user_id' => auth()->id(),
            ]);
        });
    }

    /**
     * Record a stock transfer between warehouses.
     */
    public function recordTransfer(Product $product, Warehouse $source, Warehouse $destination, int $quantity, ?string $description = null): Collection
    {
        return DB::transaction(function () use ($product, $source, $destination, $quantity, $description) {
            $description = $description ?? "Transfer from {$source->name} to {$destination->name}";

            $out = StockMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $source->id,
                'type' => 'out',
                'quantity' => $quantity,
                'description' => $description,
                'user_id' => auth()->id(),
            ]);

            $in = StockMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $destination->id,
                'type' => 'in',
                'quantity' => $quantity,
                'description' => $description,
                'user_id' => auth()->id(),
            ]);

            return collect([$out, $in]);
        });
    }
}
