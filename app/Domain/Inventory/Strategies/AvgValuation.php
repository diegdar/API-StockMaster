<?php
declare(strict_types=1);

namespace App\Domain\Inventory\Strategies;

use App\Domain\Inventory\Contracts\InventoryValuationStrategy;
use App\Models\Product;
use Illuminate\Support\Collection;

class AvgValuation implements InventoryValuationStrategy
{
    /**
     * Calculate the total valuation of a product using the Weighted Average Cost method.
     */
    public function calculate(Product $product): float
    {
        $inMovements = $this->getInMovements($product);
        $totalOutQuantity = $this->getTotalOutQuantity($product);

        return $this->calculateValuationLogic($inMovements, $totalOutQuantity);
    }

    /**
     * Get all IN stock movements for a product.
     */
    private function getInMovements(Product $product): Collection
    {
        return $product->stockMovements()
            ->where('type', 'in')
            ->whereNotNull('unit_cost')
            ->get();
    }

    /**
     * Get the total quantity of OUT stock movements for a product.
     */
    private function getTotalOutQuantity(Product $product): float
    {
        return (float) $product->stockMovements()
            ->where('type', 'out')
            ->sum('quantity');
    }

    /**
     * Core Weighted Average valuation logic.
     */
    private function calculateValuationLogic(Collection $inMovements, float $totalOut): float
    {
        $totalInQuantity = 0.0;
        $totalInCost = 0.0;

        foreach ($inMovements as $movement) {
            $totalInQuantity += (float) $movement->quantity;
            $totalInCost += (float) ($movement->quantity * $movement->unit_cost);
        }

        if ($totalInQuantity <= 0) {
            return 0.0;
        }

        $averageCost = $totalInCost / $totalInQuantity;
        $remainingQuantity = $totalInQuantity - $totalOut;

        if ($remainingQuantity <= 0) {
            return 0.0;
        }

        return (float) ($remainingQuantity * $averageCost);
    }
}
