<?php
declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;
use App\Models\Supplier;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sku' => $this->faker->unique()->bothify('PROD-####-??'),
            'name' => ucfirst($this->faker->words(3, true)),
            'description' => $this->faker->paragraph(),
            'unit_price' => $this->faker->randomFloat(2, 10, 1000),
            'unit_cost' => $this->faker->randomFloat(2, 5, 500),
            'min_stock_level' => $this->faker->numberBetween(10, 50),
            'valuation_strategy' => $this->faker->randomElement(['fifo', 'lifo', 'avg']),
            'category_id' => Category::factory(),
            'supplier_id' => Supplier::factory(),
        ];
    }
}
