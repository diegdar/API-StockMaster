<?php
declare(strict_types=1);

namespace App\Domain\Inventory\Contracts;

use App\Models\Product;

interface InventoryValuationStrategy
{
    public function calculate(Product $product): float;
}
