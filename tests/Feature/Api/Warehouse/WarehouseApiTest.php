<?php
declare(strict_types=1);

namespace Tests\Feature\Api\Warehouse;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use Laravel\Passport\Passport;
use Tests\Feature\Api\Traits\ApiTestUsersTrait;
use Tests\Feature\Api\Traits\WarehouseTestTrait;
use Tests\TestCase;

class WarehouseApiTest extends TestCase
{
    use ApiTestUsersTrait, WarehouseTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupApiUsers();
    }

    // ==========================================
    // Index Tests
    // ==========================================

    /**
     * Test authenticated users can list warehouses.
     *
     * @dataProvider usersThatCanRead
     */
    public function test_authenticated_users_can_list_warehouses(string $role): void
    {
        $user = $this->getUserByRole($role);
        Passport::actingAs($user);

        Warehouse::factory()->count(3)->create();

        $response = $this->getJson(route('warehouses.index'));

        $response->assertStatus(200);

        if ($role === 'Admin') {
            $response->assertJsonStructure([
                'data' => [
                    '*' => ['name', 'slug', 'location', 'capacity', 'is_active'],
                ],
                'meta' => ['current_page', 'total', 'per_page'],
            ]);
        }
    }

    /**
     * Test unauthenticated user cannot list warehouses.
     */
    public function test_unauthenticated_user_cannot_list_warehouses(): void
    {
        $response = $this->getJson(route('warehouses.index'));

        $response->assertStatus(401);
    }

    // ==========================================
    // Store Tests
    // ==========================================

    /**
     * Test admin can create warehouse.
     */
    public function test_admin_can_create_warehouse(): void
    {
        Passport::actingAs($this->admin);

        $data = [
            'name' => 'Almacén Central',
            'location' => 'Madrid, España',
            'capacity' => 50000,
            'is_active' => true,
        ];

        $response = $this->postJson(route('warehouses.store'), $data);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'Almacén Central',
                'location' => 'Madrid, España',
                'capacity' => 50000,
            ]);

        $this->assertDatabaseHas('warehouses', [
            'name' => 'Almacén Central',
            'slug' => 'almacen-central',
        ]);
    }

    /**
     * Test unauthorized users cannot create warehouse.
     *
     * @dataProvider usersThatCannotManage
     */
    public function test_unauthorized_users_cannot_create_warehouse(string $role): void
    {
        $user = $this->getUserByRole($role);
        Passport::actingAs($user);

        $data = [
            'name' => 'Almacén Central',
            'location' => 'Madrid, España',
        ];

        $response = $this->postJson(route('warehouses.store'), $data);

        $response->assertStatus(403);
    }

    /**
     * Test validation errors on store.
     */
    public function test_validation_errors_on_store(): void
    {
        Passport::actingAs($this->admin);

        $response = $this->postJson(route('warehouses.store'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'location']);
    }

    // ==========================================
    // Show Tests
    // ==========================================

    /**
     * Test authenticated users can view warehouse.
     *
     * @dataProvider usersThatCanRead
     */
    public function test_authenticated_users_can_view_warehouse(string $role): void
    {
        $user = $this->getUserByRole($role);
        Passport::actingAs($user);

        $warehouse = Warehouse::factory()->create();

        $response = $this->getJson(route('warehouses.show', $warehouse->id));

        $response->assertStatus(200);

        if ($role === 'Admin') {
            $response->assertJsonFragment([
                'name' => $warehouse->name,
                'slug' => $warehouse->slug,
            ]);
        }
    }

    // ==========================================
    // Update Tests
    // ==========================================

    /**
     * Test admin can update warehouse.
     */
    public function test_admin_can_update_warehouse(): void
    {
        Passport::actingAs($this->admin);

        $warehouse = Warehouse::factory()->create([
            'name' => 'Old Name',
        ]);

        $data = [
            'name' => 'New Name',
            'location' => 'New Location',
        ];

        $response = $this->putJson(route('warehouses.update', $warehouse->id), $data);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'New Name',
                'slug' => 'new-name',
            ]);

        $this->assertDatabaseHas('warehouses', [
            'id' => $warehouse->id,
            'name' => 'New Name',
        ]);
    }

    /**
     * Test unauthorized users cannot update warehouse.
     *
     * @dataProvider usersThatCannotManage
     */
    public function test_unauthorized_users_cannot_update_warehouse(string $role): void
    {
        $user = $this->getUserByRole($role);
        Passport::actingAs($user);

        $warehouse = Warehouse::factory()->create();

        $response = $this->putJson(route('warehouses.update', $warehouse->id), [
            'name' => 'New Name',
        ]);

        $response->assertStatus(403);
    }

    // ==========================================
    // Delete Tests
    // ==========================================

    /**
     * Test admin can delete warehouse without inventory.
     */
    public function test_admin_can_delete_warehouse_without_inventory(): void
    {
        Passport::actingAs($this->admin);

        $warehouse = Warehouse::factory()->create();

        $response = $this->deleteJson(route('warehouses.destroy', $warehouse->id));

        $response->assertStatus(200);

        $this->assertDatabaseMissing('warehouses', [
            'id' => $warehouse->id,
        ]);
    }

    /**
     * Test admin cannot delete warehouse with inventory.
     */
    public function test_admin_cannot_delete_warehouse_with_inventory(): void
    {
        Passport::actingAs($this->admin);

        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();
        Inventory::factory()->create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
        ]);

        $response = $this->deleteJson(route('warehouses.destroy', $warehouse->id));

        $response->assertStatus(422);
        $response->assertJsonFragment([
            'message' => 'Cannot delete warehouse with existing inventories',
        ]);
        $this->assertDatabaseHas('warehouses', [
            'id' => $warehouse->id,
        ]);
    }

    /**
     * Test unauthorized users cannot delete warehouse.
     *
     * @dataProvider usersThatCannotManage
     */
    public function test_unauthorized_users_cannot_delete_warehouse(string $role): void
    {
        $user = $this->getUserByRole($role);
        Passport::actingAs($user);

        $warehouse = Warehouse::factory()->create();

        $response = $this->deleteJson(route('warehouses.destroy', $warehouse->id));

        $response->assertStatus(403);
    }

    // ==========================================
    // Observer Tests via API
    // ==========================================

    /**
     * Test slug is auto-generated on create via API.
     */
    public function test_slug_auto_generated_on_create_via_api(): void
    {
        Passport::actingAs($this->admin);

        $response = $this->postJson(route('warehouses.store'), [
            'name' => 'Almacén Central',
            'location' => 'Madrid',
        ]);

        $response->assertJsonFragment([
            'slug' => 'almacen-central',
        ]);
    }

    /**
     * Test slug is regenerated on name update via API.
     */
    public function test_slug_regenerated_on_name_update_via_api(): void
    {
        Passport::actingAs($this->admin);

        $warehouse = Warehouse::factory()->create(['name' => 'Old Name']);

        $response = $this->putJson(route('warehouses.update', $warehouse->id), [
            'name' => 'New Name',
        ]);

        $response->assertJsonFragment([
            'slug' => 'new-name',
        ]);
    }
}
