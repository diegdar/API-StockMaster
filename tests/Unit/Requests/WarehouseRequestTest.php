<?php
declare(strict_types=1);

namespace Tests\Unit\Requests;

use App\Http\Requests\StoreWarehouseRequest;
use App\Http\Requests\UpdateWarehouseRequest;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class WarehouseRequestTest extends TestCase
{
    // ==========================================
    // StoreWarehouseRequest Tests with DataProvider
    // ==========================================

    /**
     * Data provider for StoreWarehouseRequest validation tests.
     */
    public static function storeWarehouseDataProvider(): array
    {
        return [
            'valid data with all fields' => [
                'data' => [
                    'name' => 'Almacén Central',
                    'location' => 'Madrid, España',
                    'capacity' => 50000,
                    'is_active' => true,
                ],
                'shouldPass' => true,
                'expectedErrors' => [],
            ],
            'valid data with required fields only' => [
                'data' => [
                    'name' => 'Almacén Central',
                    'location' => 'Madrid, España',
                ],
                'shouldPass' => true,
                'expectedErrors' => [],
            ],
            'missing name' => [
                'data' => [
                    'location' => 'Madrid, España',
                ],
                'shouldPass' => false,
                'expectedErrors' => ['name'],
            ],
            'missing location' => [
                'data' => [
                    'name' => 'Almacén Central',
                ],
                'shouldPass' => false,
                'expectedErrors' => ['location'],
            ],
            'negative capacity' => [
                'data' => [
                    'name' => 'Almacén Central',
                    'location' => 'Madrid, España',
                    'capacity' => -100,
                ],
                'shouldPass' => false,
                'expectedErrors' => ['capacity'],
            ],
            'name too long' => [
                'data' => [
                    'name' => str_repeat('a', 256),
                    'location' => 'Madrid, España',
                ],
                'shouldPass' => false,
                'expectedErrors' => ['name'],
            ],
            'location too long' => [
                'data' => [
                    'name' => 'Almacén Central',
                    'location' => str_repeat('a', 501),
                ],
                'shouldPass' => false,
                'expectedErrors' => ['location'],
            ],
            'capacity not integer' => [
                'data' => [
                    'name' => 'Almacén Central',
                    'location' => 'Madrid, España',
                    'capacity' => 'not-an-integer',
                ],
                'shouldPass' => false,
                'expectedErrors' => ['capacity'],
            ],
            'is_active not boolean' => [
                'data' => [
                    'name' => 'Almacén Central',
                    'location' => 'Madrid, España',
                    'is_active' => 'not-a-boolean',
                ],
                'shouldPass' => false,
                'expectedErrors' => ['is_active'],
            ],
            'zero capacity is valid' => [
                'data' => [
                    'name' => 'Almacén Central',
                    'location' => 'Madrid, España',
                    'capacity' => 0,
                ],
                'shouldPass' => true,
                'expectedErrors' => [],
            ],
        ];
    }

    /**
     * Test StoreWarehouseRequest validation with DataProvider.
     */
    #[DataProvider('storeWarehouseDataProvider')]
    public function test_store_warehouse_request_validation(
        array $data,
        bool $shouldPass,
        array $expectedErrors
    ): void {
        $request = new StoreWarehouseRequest();
        $validator = Validator::make($data, $request->rules(), $request->messages());

        $this->assertEquals($shouldPass, !$validator->fails());

        if (!$shouldPass) {
            foreach ($expectedErrors as $field) {
                $this->assertArrayHasKey($field, $validator->errors()->toArray());
            }
        }
    }

    // ==========================================
    // UpdateWarehouseRequest Tests with DataProvider
    // ==========================================

    /**
     * Data provider for UpdateWarehouseRequest validation tests.
     */
    public static function updateWarehouseDataProvider(): array
    {
        return [
            'partial data - name only' => [
                'data' => [
                    'name' => 'Nuevo Nombre',
                ],
                'shouldPass' => true,
                'expectedErrors' => [],
            ],
            'all fields' => [
                'data' => [
                    'name' => 'Nuevo Nombre',
                    'location' => 'Nueva Ubicación',
                    'capacity' => 75000,
                    'is_active' => false,
                ],
                'shouldPass' => true,
                'expectedErrors' => [],
            ],
            'empty data is valid' => [
                'data' => [],
                'shouldPass' => true,
                'expectedErrors' => [],
            ],
            'negative capacity' => [
                'data' => [
                    'capacity' => -50,
                ],
                'shouldPass' => false,
                'expectedErrors' => ['capacity'],
            ],
            'name too long' => [
                'data' => [
                    'name' => str_repeat('a', 256),
                ],
                'shouldPass' => false,
                'expectedErrors' => ['name'],
            ],
            'location too long' => [
                'data' => [
                    'location' => str_repeat('a', 501),
                ],
                'shouldPass' => false,
                'expectedErrors' => ['location'],
            ],
            'capacity not integer' => [
                'data' => [
                    'capacity' => 'not-an-integer',
                ],
                'shouldPass' => false,
                'expectedErrors' => ['capacity'],
            ],
            'is_active not boolean' => [
                'data' => [
                    'is_active' => 'not-a-boolean',
                ],
                'shouldPass' => false,
                'expectedErrors' => ['is_active'],
            ],
            'zero capacity is valid' => [
                'data' => [
                    'capacity' => 0,
                ],
                'shouldPass' => true,
                'expectedErrors' => [],
            ],
            'location only' => [
                'data' => [
                    'location' => 'Nueva Ubicación',
                ],
                'shouldPass' => true,
                'expectedErrors' => [],
            ],
            'is_active false' => [
                'data' => [
                    'is_active' => false,
                ],
                'shouldPass' => true,
                'expectedErrors' => [],
            ],
        ];
    }

    /**
     * Test UpdateWarehouseRequest validation with DataProvider.
     */
    #[DataProvider('updateWarehouseDataProvider')]
    public function test_update_warehouse_request_validation(
        array $data,
        bool $shouldPass,
        array $expectedErrors
    ): void {
        $request = new UpdateWarehouseRequest();
        $validator = Validator::make($data, $request->rules(), $request->messages());

        $this->assertEquals($shouldPass, !$validator->fails());

        if (!$shouldPass) {
            foreach ($expectedErrors as $field) {
                $this->assertArrayHasKey($field, $validator->errors()->toArray());
            }
        }
    }
}
