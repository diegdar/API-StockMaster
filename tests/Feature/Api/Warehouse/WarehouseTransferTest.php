<?php
declare(strict_types=1);

namespace Tests\Feature\Api\Warehouse;

use App\Models\Inventory;
use Laravel\Passport\Passport;
use Tests\Feature\Api\Traits\ApiTestUsersTrait;
use Tests\Feature\Api\Traits\WarehouseTestTrait;
use Tests\TestCase;

class WarehouseTransferTest extends TestCase
{
    use ApiTestUsersTrait, WarehouseTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupApiUsers();
    }

    // ==========================================
    // Transfer Tests
    // ==========================================

    /**
     * Test authenticated users can transfer stock.
     *
     * @dataProvider usersThatCanTransfer
     */
    public function test_authenticated_users_can_transfer_stock(string $role): void
    {
        Passport::actingAs($this->getUserByRole($role));
        ['product' => $product, 'source' => $source, 'destination' => $destination] = $this->setupTransferData();

        $response = $this->postJson(route('warehouses.transfer'), [
            'product_id' => $product->id,
            'source_warehouse_id' => $source->id,
            'destination_warehouse_id' => $destination->id,
            'quantity' => 10,
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Transfer completed successfully']);

        $this->assertEquals(90, Inventory::where([
            'product_id' => $product->id,
            'warehouse_id' => $source->id,
        ])->value('quantity'));

        $this->assertEquals(10, Inventory::where([
            'product_id' => $product->id,
            'warehouse_id' => $destination->id,
        ])->value('quantity'));
    }

    /**
     * Test viewer cannot transfer stock.
     */
    public function test_viewer_cannot_transfer_stock(): void
    {
        Passport::actingAs($this->viewer);
        ['product' => $product, 'source' => $source, 'destination' => $destination] = $this->setupTransferData();

        $response = $this->postJson(route('warehouses.transfer'), [
            'product_id' => $product->id,
            'source_warehouse_id' => $source->id,
            'destination_warehouse_id' => $destination->id,
            'quantity' => 10,
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test transfer with insufficient stock.
     */
    public function test_transfer_with_insufficient_stock(): void
    {
        Passport::actingAs($this->admin);
        ['product' => $product, 'source' => $source, 'destination' => $destination] = $this->setupTransferData();

        $response = $this->postJson(route('warehouses.transfer'), [
            'product_id' => $product->id,
            'source_warehouse_id' => $source->id,
            'destination_warehouse_id' => $destination->id,
            'quantity' => 150, // More than available (100)
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test transfer creates inventory in destination if not exists.
     */
    public function test_transfer_creates_inventory_in_destination(): void
    {
        Passport::actingAs($this->admin);
        ['product' => $product, 'source' => $source, 'destination' => $destination] = $this->setupTransferData();

        // Ensure no inventory exists in destination
        Inventory::where([
            'product_id' => $product->id,
            'warehouse_id' => $destination->id,
        ])->delete();

        $response = $this->postJson(route('warehouses.transfer'), [
            'product_id' => $product->id,
            'source_warehouse_id' => $source->id,
            'destination_warehouse_id' => $destination->id,
            'quantity' => 10,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('inventories', [
            'product_id' => $product->id,
            'warehouse_id' => $destination->id,
            'quantity' => 10,
        ]);
    }
}
