<?php
declare(strict_types=1);

namespace Tests\Feature\Api;

use Laravel\Passport\Passport;
use Tests\Feature\Api\Traits\ApiTestUsersTrait;
use Tests\Feature\Api\Traits\ProductApiTestTrait;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use ApiTestUsersTrait;
    use ProductApiTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupApiUsers();
    }

    /**
     * @test
     * @dataProvider productPermissionsProvider
     */
    public function test_it_verifies_product_endpoint_permissions(
        string $role,
        string $method,
        string $routeName,
        int $expectedStatus
    ): void {
        $user = $this->getUserByRole($role);
        Passport::actingAs($user);

        $entities = $this->createProductEntities();

        $data = $this->getProductRequestData($entities->category->id, $entities->supplier->id);

        $url = match ($routeName) {
            'products.index', 'products.store' => route($routeName),
            default => route($routeName, $entities->product->id),
        };

        $response = $this->json($method, $url, $data);

        $response->assertStatus($expectedStatus);
    }

    /**
     * @test
     * @dataProvider roleFieldVisibilityProvider
     */
    public function test_role_based_field_visibility(
        string $role,
        array $visibleFields,
        array $hiddenFields,
        array $assertFragment
    ): void {
        $user = $this->getUserByRole($role);
        Passport::actingAs($user);

        $entities = $this->createProductEntities();

        $response = $this->getJson(route('products.show', $entities->product->id));

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => $visibleFields]);

        if (!empty($assertFragment)) {
            $response->assertJsonFragment($assertFragment);
        }

        if (!empty($hiddenFields)) {
            $response->assertJsonMissing($hiddenFields);
        }
    }

    public function test_admin_can_delete_product_without_relations(): void
    {
        Passport::actingAs($this->admin);

        $entities = $this->createProductEntities();

        $response = $this->deleteJson(route('products.destroy', $entities->product->id));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => "The Product has been deleted successfully",
            ]);

        $this->assertDatabaseMissing('products', ['id' => $entities->product->id]);
    }

    public function test_admin_cannot_delete_product_with_inventory(): void
    {
        Passport::actingAs($this->admin);

        $entities = $this->createProductWithInventoryForDeletion();

        $response = $this->deleteJson(route('products.destroy', $entities->product->id));

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Cannot delete product because it has inventory records. Adjust inventory to zero first.',
            ]);

        $this->assertDatabaseHas('products', ['id' => $entities->product->id]);
    }

    public function test_admin_cannot_delete_product_with_stock_movements(): void
    {
        Passport::actingAs($this->admin);

        $entities = $this->createProductWithStockMovement($this->admin->id);

        $response = $this->deleteJson(route('products.destroy', $entities->product->id));

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Cannot delete product because it has inventory records. Adjust inventory to zero first.',
            ]);

        $this->assertDatabaseHas('products', ['id' => $entities->product->id]);
    }

    public function test_admin_cannot_delete_product_with_active_alerts(): void
    {
        Passport::actingAs($this->admin);

        $entities = $this->createProductWithRestockAlert();

        $response = $this->deleteJson(route('products.destroy', $entities->product->id));

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Cannot delete product because it has active restock alerts. Resolve alerts first.',
            ]);

        $this->assertDatabaseHas('products', ['id' => $entities->product->id]);
    }

    public function test_it_can_get_product_by_slug(): void
    {
        $entities = $this->createProductEntities();
        Passport::actingAs($this->admin);

        $response = $this->getJson(route('products.show-by-sku', $entities->product->sku));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'sku' => $entities->product->sku,
            ]);
    }
}
