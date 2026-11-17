<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
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
            'size' => (string) fake()->randomElement([28, 30, 32, 34, 36]),
            'inseam' => fake()->randomElement(['30L', '32L', '34L']),
            'sku' => 'RJ-'.Str::upper(Str::random(8)),
            'price_override' => null,
            'stock_quantity' => 10,
        ];
    }

    /**
     * Indicate that the variant has no stock left.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => 0,
        ]);
    }
}
