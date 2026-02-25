<?php
declare(strict_types=1);

namespace Tests\Feature\Api;

use Laravel\Passport\Passport;
use Tests\TestCase;
use Tests\Traits\ApiTestUsersTrait;
use Tests\Traits\CategoryTestTrait;

class CategoryApiTest extends TestCase
{
    use ApiTestUsersTrait;
    use CategoryTestTrait;

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
        $category = $this->createCategory();

        $url = in_array($routeName, ['categories.index', 'categories.store'])
            ? route($routeName)
            : route($routeName, $category->id);

        $response = $this->json($method, $url, ['name' => 'Test', 'description' => 'Test']);

        $response->assertStatus($expectedStatus);
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
        $category = $this->createCategoryWithSlug('Original Name', 'original-name');

        $response = $this->putJson(route('categories.update', $category->id), ['name' => 'Updated Name']);

        $response->assertStatus(200)->assertJsonFragment([
            'name' => 'Updated Name',
            'slug' => 'updated-name',
        ]);
    }

    public function test_observer_handles_duplicate_slugs(): void
    {
        Passport::actingAs($this->admin);
        $this->createCategoryWithSlug('Test Category', 'test-category');

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
        $category = $this->createCategory();

        $response = $this->getJson(route('categories.show', $category->id));

        $response->assertStatus(200)->assertJsonStructure([
            'data' => ['name', 'slug', 'description', 'products_count'],
        ]);
    }

    public function test_categories_list_includes_pagination(): void
    {
        Passport::actingAs($this->admin);
        $this->createCategories(20);

        $response = $this->getJson(route('categories.index'));

        $response->assertStatus(200)->assertJsonStructure(['data', 'links', 'meta']);
    }

    public function test_admin_cannot_delete_category_with_products(): void
    {
        Passport::actingAs($this->admin);
        $entities = $this->createCategoryWithProducts(3);

        $response = $this->deleteJson(route('categories.destroy', $entities->category->id));

        $response->assertStatus(422)->assertJsonFragment([
            'message' => 'Cannot delete category because it has associated products. Please remove or reassign the products first.',
        ]);
        $this->assertCategoryExists($entities->category->id);
    }

    public function test_admin_can_delete_category_without_products(): void
    {
        Passport::actingAs($this->admin);
        $category = $this->createCategory();

        $response = $this->deleteJson(route('categories.destroy', $category->id));

        $response->assertStatus(200);
        $this->assertCategoryDeleted($category->id);
    }

    public function test_show_by_slug_returns_category(): void
    {
        Passport::actingAs($this->admin);
        $category = $this->createCategoryWithSlug('Electronics', 'electronics');

        $response = $this->getJson(route('categories.show-by-slug', $category->slug));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Electronics',
                'slug' => 'electronics',
            ]);
    }

    public function test_show_by_slug_returns_404_for_non_existent_slug(): void
    {
        Passport::actingAs($this->admin);

        $response = $this->getJson(route('categories.show-by-slug', 'non-existent-slug'));

        $response->assertStatus(404);
    }
}
