<?php
declare(strict_types=1);

namespace Tests\Feature\Api\Traits;


use Illuminate\Testing\Fluent\AssertableJson;

trait ProductApiTestTrait
{
    /**
     * Data provider for role-based field visibility tests.
     */
    public static function roleFieldVisibilityProvider(): array
    {
        return [
            'Admin sees all fields including sensitive data' => [
                'role' => 'Admin',
                'visibleFields' => [
                    'id', 'name', 'sku', 'description', 'category_id',
                    'created_at', 'updated_at', 'unit_price', 'supplier_id',
                    'valuation_strategy', 'unit_cost', 'margin', 'margin_percentage',
                ],
                'hiddenFields' => [],
                'assertFragment' => ['unit_cost' => 50.00, 'margin' => 50.00],
            ],
            'Worker sees operational fields but not sensitive financial data' => [
                'role' => 'Worker',
                'visibleFields' => [
                    'id', 'name', 'sku', 'description', 'category_id',
                    'created_at', 'updated_at', 'unit_price', 'supplier_id',
                    'valuation_strategy',
                ],
                'hiddenFields' => ['unit_cost', 'margin', 'margin_percentage'],
                'assertFragment' => ['unit_price' => 100.00],
            ],
            'Viewer sees only public fields' => [
                'role' => 'Viewer',
                'visibleFields' => [
                    'id', 'name', 'sku', 'description', 'category_id',
                    'created_at', 'updated_at',
                ],
                'hiddenFields' => ['unit_price', 'unit_cost', 'margin', 'margin_percentage'],
                'assertFragment' => [],
            ],
        ];
    }

    /**
     * Data provider for product deletion restriction tests.
     */
    public static function productDeletionRestrictionProvider(): array
    {
        return [
            'cannot delete product with inventory' => [
                'expectedMessage' => 'Cannot delete product because it has inventory records. Adjust inventory to zero first.',
            ],
            'cannot delete product with stock movements' => [
                'expectedMessage' => 'Cannot delete product because it has inventory records. Adjust inventory to zero first.',
            ],
            'cannot delete product with active alerts' => [
                'expectedMessage' => 'Cannot delete product because it has active restock alerts. Resolve alerts first.',
            ],
        ];
    }

    public static function productPermissionsProvider(): array
    {
        return [
            'Admin can list products' => ['Admin', 'GET', 'products.index', 200],
            'Admin can show product' => ['Admin', 'GET', 'products.show', 200],
            'Admin can create product' => ['Admin', 'POST', 'products.store', 201],
            'Admin can update product' => ['Admin', 'PUT', 'products.update', 200],
            'Admin can delete product' => ['Admin', 'DELETE', 'products.destroy', 200],
            'Worker can list products' => ['Worker', 'GET', 'products.index', 200],
            'Worker can show product' => ['Worker', 'GET', 'products.show', 200],
            'Worker cannot create product' => ['Worker', 'POST', 'products.store', 403],
            'Worker cannot update product' => ['Worker', 'PUT', 'products.update', 403],
            'Worker cannot delete product' => ['Worker', 'DELETE', 'products.destroy', 403],
            'Viewer can list products' => ['Viewer', 'GET', 'products.index', 200],
            'Viewer can show product' => ['Viewer', 'GET', 'products.show', 200],
            'Viewer cannot create product' => ['Viewer', 'POST', 'products.store', 403],
            'Viewer cannot update product' => ['Viewer', 'PUT', 'products.update', 403],
            'Viewer cannot delete product' => ['Viewer', 'DELETE', 'products.destroy', 403],
        ];
    }

    /**
     * Assert product JSON structure for given fields.
     *
     * @param AssertableJson $json
     * @param array<int, string> $fields
     */
    protected function assertProductJsonStructure(AssertableJson $json, array $fields): void
    {
        $json->has('data', fn (AssertableJson $json) => $json->hasAll($fields));
    }
}
