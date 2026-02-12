<?php
declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockMovement>
 */
class StockMovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(['in', 'out', 'transfer', 'audit']),
            'quantity' => $this->faker->numberBetween(1, 100),
            'unit_cost' => $this->faker->randomFloat(2, 5, 500),
            'description' => $this->faker->sentence(),
            'product_id' => Product::factory(),
            'warehouse_id' => Warehouse::factory(),
            'user_id' => User::factory(),
        ];
    }
}
