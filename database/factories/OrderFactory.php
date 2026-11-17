<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state: a guest order for one $100 item plus
     * $12 shipping, with GST as 1/11 of the GST-inclusive total.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $street = fake()->streetAddress();
        $suburb = fake()->city();

        return [
            'order_number' => 'RJ-'.now()->format('ymd').'-'.Str::upper(Str::random(5)),
            'user_id' => null,
            'email' => fake()->unique()->safeEmail(),
            'shipping_name' => fake()->firstName().' '.fake()->lastName(),
            'shipping_address' => "{$street}, {$suburb} NSW 2000",
            'shipping_street' => $street,
            'shipping_suburb' => $suburb,
            'shipping_state' => 'NSW',
            'shipping_postcode' => '2000',
            'status' => 'pending',
            'subtotal' => 100.00,
            'shipping_cost' => 12.00,
            'gst' => 10.18,
            'total' => 112.00,
            'discount_code' => null,
            'discount_amount' => null,
        ];
    }

    /**
     * Indicate that the order has been paid via Stripe.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'stripe_checkout_session_id' => 'cs_test_'.Str::random(24),
            'stripe_payment_intent_id' => 'pi_test_'.Str::random(24),
        ]);
    }

    /**
     * Indicate that the order has been delivered.
     */
    public function delivered(): static
    {
        return $this->paid()->state(fn (array $attributes) => [
            'status' => 'delivered',
            'shipped_at' => now()->subDays(5),
            'delivered_at' => now()->subDays(2),
        ]);
    }
}
