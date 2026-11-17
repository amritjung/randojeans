<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
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
        $name = 'The '.Str::title(fake()->unique()->words(2, true)).' Jean';

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'base_price' => 180.00,
            'sale_price' => null,
            'status' => 'active',
            'fit' => 'Relaxed Straight',
            'composition' => '100% Cotton',
            'weight_oz' => 14.5,
            'weave' => '3x1 Right-Hand Twill',
            'origin' => 'Woven and sewn in Vietnam',
        ];
    }

    /**
     * Indicate that the product is a draft and not visible in the store.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    /**
     * Indicate that the product has been archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
        ]);
    }

    /**
     * Indicate that the product is on sale at the given price.
     */
    public function onSale(float $salePrice = 150.00): static
    {
        return $this->state(fn (array $attributes) => [
            'sale_price' => $salePrice,
        ]);
    }
}
