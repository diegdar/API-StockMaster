<?php
declare(strict_types=1);

namespace Tests\Feature\Api\Warehouse;

use App\Models\Warehouse;
use Laravel\Passport\Passport;
use Tests\TestCase;
use Tests\Traits\ApiTestUsersTrait;
use Tests\Traits\WarehouseTestTrait;

class WarehouseValidationTest extends TestCase
{
    use ApiTestUsersTrait;
    use WarehouseTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupApiUsers();
    }

    // ==========================================
    // StoreWarehouseRequest Validation Tests
    // ==========================================

    /**
     * Data provider for StoreWarehouseRequest validation.
     *
     * @return array<string, array{data: array<string, mixed>, expectedErrors: array<int, string>}>
     */
    public static function storeWarehouseValidationProvider(): array
    {
        return [
            'name is required' => [
                'data' => ['location' => 'Madrid, España'],
                'expectedErrors' => ['name'],
            ],
            'name must be string' => [
                'data' => ['name' => 12345, 'location' => 'Madrid, España'],
                'expectedErrors' => ['name'],
            ],
            'name exceeds max length' => [
                'data' => ['name' => str_repeat('a', 256), 'location' => 'Madrid, España'],
                'expectedErrors' => ['name'],
            ],
            'location is required' => [
                'data' => ['name' => 'Almacén Central'],
                'expectedErrors' => ['location'],
            ],
            'location must be string' => [
                'data' => ['name' => 'Almacén Central', 'location' => 12345],
                'expectedErrors' => ['location'],
            ],
            'location exceeds max length' => [
                'data' => ['name' => 'Almacén Central', 'location' => str_repeat('a', 501)],
                'expectedErrors' => ['location'],
            ],
            'capacity must be integer' => [
                'data' => ['name' => 'Almacén Central', 'location' => 'Madrid', 'capacity' => 'not-integer'],
                'expectedErrors' => ['capacity'],
            ],
            'capacity must be non-negative' => [
                'data' => ['name' => 'Almacén Central', 'location' => 'Madrid', 'capacity' => -1],
                'expectedErrors' => ['capacity'],
            ],
            'is_active must be boolean' => [
                'data' => ['name' => 'Almacén Central', 'location' => 'Madrid', 'is_active' => 'not-boolean'],
                'expectedErrors' => ['is_active'],
            ],
            'multiple validation errors' => [
                'data' => ['name' => str_repeat('a', 256), 'location' => 12345],
                'expectedErrors' => ['name', 'location'],
            ],
        ];
    }

    /**
     * Test StoreWarehouseRequest validation errors.
     *
     * @dataProvider storeWarehouseValidationProvider
     */
    public function test_store_warehouse_validation_errors(array $data, array $expectedErrors): void
    {
        Passport::actingAs($this->admin);

        $response = $this->postJson(route('warehouses.store'), $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors($expectedErrors);
    }

    /**
     * Test store warehouse with unique name violation.
     */
    public function test_store_warehouse_unique_name_validation(): void
    {
        Passport::actingAs($this->admin);

        Warehouse::factory()->create(['name' => 'Existing Warehouse']);

        $response = $this->postJson(route('warehouses.store'), [
            'name' => 'Existing Warehouse',
            'location' => 'Madrid, España',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    // ==========================================
    // UpdateWarehouseRequest Validation Tests
    // ==========================================

    /**
     * Data provider for UpdateWarehouseRequest validation.
     *
     * @return array<string, array{data: array<string, mixed>, expectedErrors: array<int, string>}>
     */
    public static function updateWarehouseValidationProvider(): array
    {
        return [
            'name must be string' => [
                'data' => ['name' => 12345],
                'expectedErrors' => ['name'],
            ],
            'name exceeds max length' => [
                'data' => ['name' => str_repeat('a', 256)],
                'expectedErrors' => ['name'],
            ],
            'location must be string' => [
                'data' => ['location' => 12345],
                'expectedErrors' => ['location'],
            ],
            'location exceeds max length' => [
                'data' => ['location' => str_repeat('a', 501)],
                'expectedErrors' => ['location'],
            ],
            'capacity must be integer' => [
                'data' => ['capacity' => 'not-integer'],
                'expectedErrors' => ['capacity'],
            ],
            'capacity must be non-negative' => [
                'data' => ['capacity' => -1],
                'expectedErrors' => ['capacity'],
            ],
            'is_active must be boolean' => [
                'data' => ['is_active' => 'not-boolean'],
                'expectedErrors' => ['is_active'],
            ],
            'multiple validation errors' => [
                'data' => ['name' => str_repeat('a', 256), 'capacity' => -5],
                'expectedErrors' => ['name', 'capacity'],
            ],
        ];
    }

    /**
     * Test UpdateWarehouseRequest validation errors.
     *
     * @dataProvider updateWarehouseValidationProvider
     */
    public function test_update_warehouse_validation_errors(array $data, array $expectedErrors): void
    {
        Passport::actingAs($this->admin);

        $warehouse = Warehouse::factory()->create();

        $response = $this->putJson(route('warehouses.update', $warehouse->id), $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors($expectedErrors);
    }

    // ==========================================
    // TransferStockRequest Validation Tests
    // ==========================================

    /**
     * Data provider for TransferStockRequest validation.
     *
     * @return array<string, array{data: array<string, mixed>, expectedErrors: array<int, string>}>
     */
    public static function transferStockValidationProvider(): array
    {
        return [
            'product_id is required' => [
                'data' => [
                    'source_warehouse_id' => 1,
                    'destination_warehouse_id' => 2,
                    'quantity' => 10,
                ],
                'expectedErrors' => ['product_id'],
            ],
            'product_id must be integer' => [
                'data' => [
                    'product_id' => 'not-integer',
                    'source_warehouse_id' => 1,
                    'destination_warehouse_id' => 2,
                    'quantity' => 10,
                ],
                'expectedErrors' => ['product_id'],
            ],
            'product_id must exist in products table' => [
                'data' => [
                    'product_id' => 99999,
                    'source_warehouse_id' => 1,
                    'destination_warehouse_id' => 2,
                    'quantity' => 10,
                ],
                'expectedErrors' => ['product_id'],
            ],
            'source_warehouse_id is required' => [
                'data' => [
                    'product_id' => 1,
                    'destination_warehouse_id' => 2,
                    'quantity' => 10,
                ],
                'expectedErrors' => ['source_warehouse_id'],
            ],
            'source_warehouse_id must be integer' => [
                'data' => [
                    'product_id' => 1,
                    'source_warehouse_id' => 'not-integer',
                    'destination_warehouse_id' => 2,
                    'quantity' => 10,
                ],
                'expectedErrors' => ['source_warehouse_id'],
            ],
            'source_warehouse_id must exist in warehouses table' => [
                'data' => [
                    'product_id' => 1,
                    'source_warehouse_id' => 99999,
                    'destination_warehouse_id' => 2,
                    'quantity' => 10,
                ],
                'expectedErrors' => ['source_warehouse_id'],
            ],
            'destination_warehouse_id is required' => [
                'data' => [
                    'product_id' => 1,
                    'source_warehouse_id' => 1,
                    'quantity' => 10,
                ],
                'expectedErrors' => ['destination_warehouse_id'],
            ],
            'destination_warehouse_id must be integer' => [
                'data' => [
                    'product_id' => 1,
                    'source_warehouse_id' => 1,
                    'destination_warehouse_id' => 'not-integer',
                    'quantity' => 10,
                ],
                'expectedErrors' => ['destination_warehouse_id'],
            ],
            'destination_warehouse_id must exist in warehouses table' => [
                'data' => [
                    'product_id' => 1,
                    'source_warehouse_id' => 1,
                    'destination_warehouse_id' => 99999,
                    'quantity' => 10,
                ],
                'expectedErrors' => ['destination_warehouse_id'],
            ],
            'source and destination must be different' => [
                'data' => [
                    'product_id' => 1,
                    'source_warehouse_id' => 1,
                    'destination_warehouse_id' => 1,
                    'quantity' => 10,
                ],
                'expectedErrors' => ['source_warehouse_id', 'destination_warehouse_id'],
            ],
            'quantity is required' => [
                'data' => [
                    'product_id' => 1,
                    'source_warehouse_id' => 1,
                    'destination_warehouse_id' => 2,
                ],
                'expectedErrors' => ['quantity'],
            ],
            'quantity must be integer' => [
                'data' => [
                    'product_id' => 1,
                    'source_warehouse_id' => 1,
                    'destination_warehouse_id' => 2,
                    'quantity' => 'not-integer',
                ],
                'expectedErrors' => ['quantity'],
            ],
            'quantity must be at least 1' => [
                'data' => [
                    'product_id' => 1,
                    'source_warehouse_id' => 1,
                    'destination_warehouse_id' => 2,
                    'quantity' => 0,
                ],
                'expectedErrors' => ['quantity'],
            ],
            'quantity cannot be negative' => [
                'data' => [
                    'product_id' => 1,
                    'source_warehouse_id' => 1,
                    'destination_warehouse_id' => 2,
                    'quantity' => -5,
                ],
                'expectedErrors' => ['quantity'],
            ],
            'description must be string' => [
                'data' => [
                    'product_id' => 1,
                    'source_warehouse_id' => 1,
                    'destination_warehouse_id' => 2,
                    'quantity' => 10,
                    'description' => 12345,
                ],
                'expectedErrors' => ['description'],
            ],
            'description exceeds max length' => [
                'data' => [
                    'product_id' => 1,
                    'source_warehouse_id' => 1,
                    'destination_warehouse_id' => 2,
                    'quantity' => 10,
                    'description' => str_repeat('a', 501),
                ],
                'expectedErrors' => ['description'],
            ],
            'multiple validation errors' => [
                'data' => [
                    'product_id' => 'invalid',
                    'source_warehouse_id' => 1,
                    'destination_warehouse_id' => 1,
                    'quantity' => -5,
                ],
                'expectedErrors' => ['product_id', 'source_warehouse_id', 'destination_warehouse_id', 'quantity'],
            ],
        ];
    }

    /**
     * Test TransferStockRequest validation errors.
     *
     * @dataProvider transferStockValidationProvider
     */
    public function test_transfer_stock_validation_errors(array $data, array $expectedErrors): void
    {
        Passport::actingAs($this->admin);

        $response = $this->postJson(route('warehouses.transfer'), $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors($expectedErrors);
    }

    /**
     * Test transfer to inactive warehouse fails validation.
     */
    public function test_transfer_to_inactive_warehouse_fails(): void
    {
        Passport::actingAs($this->admin);

        ['source' => $source, 'destination' => $destination, 'product' => $product] = $this->setupTransferData();

        // Make destination inactive
        $destination->update(['is_active' => false]);

        $response = $this->postJson(route('warehouses.transfer'), [
            'product_id' => $product->id,
            'source_warehouse_id' => $source->id,
            'destination_warehouse_id' => $destination->id,
            'quantity' => 10,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['destination_warehouse_id']);
    }
}
