<?php
declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;
use App\Models\Warehouse;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RestockAlert>
 */
class RestockAlertFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'warehouse_id' => Warehouse::factory(),
            'threshold' => $this->faker->numberBetween(5, 50),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
