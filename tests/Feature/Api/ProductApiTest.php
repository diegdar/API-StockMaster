<?php
declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Inventory;
use App\Models\StockMovement;
use App\Models\RestockAlert;
use Database\Seeders\RoleAndPermissionSeeder;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    /**
     * @test
     * @dataProvider productPermissionsProvider
     */
    public function test_it_verifies_product_endpoint_permissions(string $role, string $method, string $routeName, int $expectedStatus)
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        Passport::actingAs($user);

        $product = Product::factory()->create();
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();

        $data = [
            'name' => 'Test Product',
            'sku' => 'TEST-SKU-' . uniqid(),
            'description' => 'Test description',
            'unit_price' => 100.00,
            'unit_cost' => 50.00,
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'valuation_strategy' => 'fifo',
        ];

        $url = match ($routeName) {
            'products.index', 'products.store' => route($routeName),
            default => route($routeName, $product->id),
        };

        $response = $this->json($method, $url, $data);

        $response->assertStatus($expectedStatus);
    }

    public static function productPermissionsProvider(): array
    {
        return [
            // Admin: Full Access
            'Admin can list products' => ['Admin', 'GET', 'products.index', 200],
            'Admin can show product' => ['Admin', 'GET', 'products.show', 200],
            'Admin can create product' => ['Admin', 'POST', 'products.store', 201],
            'Admin can update product' => ['Admin', 'PUT', 'products.update', 200],
            'Admin can delete product' => ['Admin', 'DELETE', 'products.destroy', 200],

            // Worker: Read-only Access (for catalog)
            'Worker can list products' => ['Worker', 'GET', 'products.index', 200],
            'Worker can show product' => ['Worker', 'GET', 'products.show', 200],
            'Worker cannot create product' => ['Worker', 'POST', 'products.store', 403],
            'Worker cannot update product' => ['Worker', 'PUT', 'products.update', 403],
            'Worker cannot delete product' => ['Worker', 'DELETE', 'products.destroy', 403],

            // Viewer: Read-only Access
            'Viewer can list products' => ['Viewer', 'GET', 'products.index', 200],
            'Viewer can show product' => ['Viewer', 'GET', 'products.show', 200],
            'Viewer cannot create product' => ['Viewer', 'POST', 'products.store', 403],
            'Viewer cannot update product' => ['Viewer', 'PUT', 'products.update', 403],
            'Viewer cannot delete product' => ['Viewer', 'DELETE', 'products.destroy', 403],
        ];
    }

    public function test_admin_sees_all_fields_including_sensitive_data()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        Passport::actingAs($admin);

        $product = Product::factory()->create([
            'unit_price' => 100.00,
            'unit_cost' => 50.00,
        ]);

        $response = $this->getJson(route('products.show', $product->id));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'sku',
                    'description',
                    'category_id',
                    'created_at',
                    'updated_at',
                    'unit_price',
                    'supplier_id',
                    'valuation_strategy',
                    'unit_cost',
                    'margin',
                    'margin_percentage',
                ],
            ])
            ->assertJsonFragment([
                'unit_cost' => 50.00,
                'margin' => 50.00,
            ]);
    }

    public function test_worker_sees_operational_fields_but_not_sensitive_financial_data()
    {
        $worker = User::factory()->create();
        $worker->assignRole('Worker');
        Passport::actingAs($worker);

        $product = Product::factory()->create([
            'unit_price' => 100.00,
            'unit_cost' => 50.00,
        ]);

        $response = $this->getJson(route('products.show', $product->id));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'sku',
                    'description',
                    'category_id',
                    'created_at',
                    'updated_at',
                    'unit_price',
                    'supplier_id',
                    'valuation_strategy',
                ],
            ])
            ->assertJsonFragment([
                'unit_price' => 100.00,
            ])
            ->assertJsonMissing([
                'unit_cost' => 50.00,
                'margin' => 50.00,
            ]);
    }

    public function test_viewer_sees_only_public_fields()
    {
        $viewer = User::factory()->create();
        $viewer->assignRole('Viewer');
        Passport::actingAs($viewer);

        $product = Product::factory()->create([
            'unit_price' => 100.00,
            'unit_cost' => 50.00,
        ]);

        $response = $this->getJson(route('products.show', $product->id));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'sku',
                    'description',
                    'category_id',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonMissing([
                'unit_price' => 100.00,
                'unit_cost' => 50.00,
                'margin' => 50.00,
            ]);
    }

    public function test_admin_can_delete_product_without_relations()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        Passport::actingAs($admin);

        $product = Product::factory()->create();

        $response = $this->deleteJson(route('products.destroy', $product->id));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => "The Product has been deleted successfully",
            ]);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_admin_cannot_delete_product_with_inventory()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        Passport::actingAs($admin);

        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();
        Inventory::factory()->create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 100,
        ]);

        $response = $this->deleteJson(route('products.destroy', $product->id));

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Cannot delete product because it has inventory records. Adjust inventory to zero first.',
            ]);

        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_admin_cannot_delete_product_with_stock_movements()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        Passport::actingAs($admin);

        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();
        StockMovement::factory()->create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'user_id' => $admin->id,
            'type' => 'in',
            'quantity' => 50,
        ]);

        // StockMovementObserver creates Inventory, so inventory check fails first
        $response = $this->deleteJson(route('products.destroy', $product->id));

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Cannot delete product because it has inventory records. Adjust inventory to zero first.',
            ]);

        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_admin_cannot_delete_product_with_active_alerts()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        Passport::actingAs($admin);

        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();
        RestockAlert::factory()->create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'is_active' => true,
            'threshold' => 10,
        ]);

        $response = $this->deleteJson(route('products.destroy', $product->id));

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Cannot delete product because it has active restock alerts. Resolve alerts first.',
            ]);

        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }
}
