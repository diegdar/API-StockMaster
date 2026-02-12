<?php
declare(strict_types=1);

namespace App\Domain\Inventory\Services;

use App\Domain\Inventory\Factories\ValuationStrategyFactory;
use App\Models\Product;

class InventoryValuationService
{
    public function __construct(
        private readonly ValuationStrategyFactory $factory
    ) {
    }

    /**
     * Calculate the total inventory valuation for a product based on its assigned strategy.
     */
    public function calculate(Product $product): float
    {
        $strategy = $this->factory->make($product->valuation_strategy ?? 'fifo');

        return $strategy->calculate($product);
    }
}
