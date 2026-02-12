<?php
declare(strict_types=1);

namespace App\Domain\Inventory\Factories;

use App\Domain\Inventory\Contracts\InventoryValuationStrategy;
use App\Domain\Inventory\Strategies\AvgValuation;
use App\Domain\Inventory\Strategies\FifoValuation;
use App\Domain\Inventory\Strategies\LifoValuation;
use InvalidArgumentException;

class ValuationStrategyFactory
{
    public function make(string $strategy): InventoryValuationStrategy
    {
        return match (strtolower($strategy)) {
            'fifo' => new FifoValuation(),
            'lifo' => new LifoValuation(),
            'avg' => new AvgValuation(),
            default => throw new InvalidArgumentException("Unknown valuation strategy: {$strategy}"),
        };
    }
}
