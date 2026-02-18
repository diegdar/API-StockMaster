<?php
declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use Laravel\Passport\Passport;
use Tests\Feature\Api\Traits\ApiTestUsersTrait;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use ApiTestUsersTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupApiUsers();
    }

    /**
     * @test
     * @dataProvider categoryPermissionsProvider
     */
    public function it_verifies_category_endpoint_permissions(
        string $role,
        string $method,
        string $routeName,
        int $expectedStatus
    ): void {
        Passport::actingAs($this->getUserByRole($role));
        $category = Category::factory()->create();

        $url = in_array($routeName, ['categories.index', 'categories.store'])
            ? route($routeName)
            : route($routeName, $category->id);

        $response = $this->json($method, $url, ['name' => 'Test', 'description' => 'Test']);

        $response->assertStatus($expectedStatus);
    }

    public static function categoryPermissionsProvider(): array
    {
        return [
            'Admin: Full Access' => ['Admin', 'GET', 'categories.index', 200],
            'Admin: Can show' => ['Admin', 'GET', 'categories.show', 200],
            'Admin: Can create' => ['Admin', 'POST', 'categories.store', 201],
            'Admin: Can update' => ['Admin', 'PUT', 'categories.update', 200],
            'Admin: Can delete without products' => ['Admin', 'DELETE', 'categories.destroy', 204],
            'Worker: Read-only' => ['Worker', 'GET', 'categories.index', 200],
            'Worker: Can show' => ['Worker', 'GET', 'categories.show', 200],
            'Worker: Cannot create' => ['Worker', 'POST', 'categories.store', 403],
            'Worker: Cannot update' => ['Worker', 'PUT', 'categories.update', 403],
            'Worker: Cannot delete' => ['Worker', 'DELETE', 'categories.destroy', 403],
            'Viewer: Read-only' => ['Viewer', 'GET', 'categories.index', 200],
            'Viewer: Can show' => ['Viewer', 'GET', 'categories.show', 200],
            'Viewer: Cannot create' => ['Viewer', 'POST', 'categories.store', 403],
            'Viewer: Cannot update' => ['Viewer', 'PUT', 'categories.update', 403],
            'Viewer: Cannot delete' => ['Viewer', 'DELETE', 'categories.destroy', 403],
        ];
    }

    public function test_observer_generates_slug_on_create(): void
    {
        Passport::actingAs($this->admin);

        $response = $this->postJson(route('categories.store'), [
            'name' => 'Ferretería General',
            'description' => 'Artículos de ferretería general',
        ]);

        $response->assertStatus(201)->assertJsonFragment([
            'name' => 'Ferretería General',
            'slug' => 'ferreteria-general',
        ]);
    }

    public function test_observer_regenerates_slug_on_name_change(): void
    {
        Passport::actingAs($this->admin);
        $category = Category::factory()->create(['name' => 'Original Name', 'slug' => 'original-name']);

        $response = $this->putJson(route('categories.update', $category->id), ['name' => 'Updated Name']);

        $response->assertStatus(200)->assertJsonFragment([
            'name' => 'Updated Name',
            'slug' => 'updated-name',
        ]);
    }

    public function test_observer_handles_duplicate_slugs(): void
    {
        Passport::actingAs($this->admin);
        Category::factory()->create(['name' => 'Test Category', 'slug' => 'test-category']);

        $response = $this->postJson(route('categories.store'), ['name' => 'Test Category']);

        $response->assertStatus(201)->assertJsonFragment(['slug' => 'test-category-1']);
    }

    public function test_validation_name_required(): void
    {
        Passport::actingAs($this->admin);

        $response = $this->postJson(route('categories.store'), []);

        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Validation failed'])
            ->assertJsonStructure(['errors' => ['name']]);
    }

    public function test_category_resource_structure(): void
    {
        Passport::actingAs($this->admin);
        $category = Category::factory()->create();

        $response = $this->getJson(route('categories.show', $category->id));

        $response->assertStatus(200)->assertJsonStructure([
            'data' => ['name', 'slug', 'description', 'products_count'],
        ]);
    }

    public function test_categories_list_includes_pagination(): void
    {
        Passport::actingAs($this->admin);
        Category::factory()->count(20)->create();

        $response = $this->getJson(route('categories.index'));

        $response->assertStatus(200)->assertJsonStructure(['data', 'links', 'meta']);
    }

    public function test_admin_cannot_delete_category_with_products(): void
    {
        Passport::actingAs($this->admin);
        $category = Category::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $category->id]);

        $response = $this->deleteJson(route('categories.destroy', $category->id));

        $response->assertStatus(422)->assertJsonFragment([
            'message' => 'Cannot delete category because it has associated products. Please remove or reassign the products first.',
        ]);
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_admin_can_delete_category_without_products(): void
    {
        Passport::actingAs($this->admin);
        $category = Category::factory()->create();

        $response = $this->deleteJson(route('categories.destroy', $category->id));

        $response->assertStatus(204);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
