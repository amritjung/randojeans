<?php

use App\Models\Discount;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;

test('each factory persists a valid record', function (string $model) {
    $record = $model::factory()->create();

    $this->assertModelExists($record);
})->with([
    'user' => User::class,
    'product' => Product::class,
    'product variant' => ProductVariant::class,
    'order' => Order::class,
    'order item' => OrderItem::class,
    'discount' => Discount::class,
]);

test('product factory states set the expected status and price', function () {
    expect(Product::factory()->draft()->create()->isActive())->toBeFalse()
        ->and(Product::factory()->archived()->create()->status)->toBe('archived')
        ->and(Product::factory()->onSale(120)->create()->currentPrice())->toBe(120.0);
});

test('order factory totals are internally consistent', function () {
    $order = Order::factory()->create();

    expect((float) $order->total)->toBe((float) $order->subtotal + (float) $order->shipping_cost)
        ->and((float) $order->gst)->toBe(round((float) $order->total / 11, 2));
});

test('delivered orders are within the return window', function () {
    $order = Order::factory()->delivered()->create();

    expect($order->isReturnEligible())->toBeTrue();
});

test('discount factory states make a code invalid', function () {
    expect(Discount::factory()->create()->isValid())->toBeTrue()
        ->and(Discount::factory()->inactive()->create()->isValid())->toBeFalse()
        ->and(Discount::factory()->expired()->create()->isValid())->toBeFalse();
});
