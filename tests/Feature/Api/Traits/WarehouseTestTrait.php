<?php
declare(strict_types=1);

namespace Tests\Feature\Api\Traits;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;

trait WarehouseTestTrait
{
    /**
     * Setup transfer test data.
     *
     * @return array{product: Product, source: Warehouse, destination: Warehouse}
     */
    protected function setupTransferData(): array
    {
        $source = Warehouse::factory()->create(['is_active' => true]);
        $destination = Warehouse::factory()->create(['is_active' => true]);
        $product = Product::factory()->create();
        Inventory::factory()->create([
            'product_id' => $product->id,
            'warehouse_id' => $source->id,
            'quantity' => 100,
        ]);

        return compact('product', 'source', 'destination');
    }

    /**
     * Data provider for users that can read resources.
     *
     * @return array<string, array{0: string}>
     */
    public static function usersThatCanRead(): array
    {
        return [
            'Admin' => ['Admin'],
            'Worker' => ['Worker'],
            'Viewer' => ['Viewer'],
        ];
    }

    /**
     * Data provider for users that cannot create/update/delete resources.
     *
     * @return array<string, array{0: string}>
     */
    public static function usersThatCannotManage(): array
    {
        return [
            'Worker' => ['Worker'],
            'Viewer' => ['Viewer'],
        ];
    }

    /**
     * Data provider for users that can transfer stock.
     *
     * @return array<string, array{0: string}>
     */
    public static function usersThatCanTransfer(): array
    {
        return [
            'Admin' => ['Admin'],
            'Worker' => ['Worker'],
        ];
    }
}
