<?php

use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Str;

/**
 * Seeds a cart for a guest by giving the request a known session cookie,
 * since carts are keyed by session ID.
 */
function withGuestCart(ProductVariant $variant): string
{
    $sessionId = Str::random(40);

    CartItem::create([
        'session_id' => $sessionId,
        'product_variant_id' => $variant->id,
        'quantity' => 1,
    ]);

    return $sessionId;
}

test('home page renders', function () {
    ProductVariant::factory()->create();

    $this->get(route('home'))->assertOk();
});

test('shop page renders', function () {
    ProductVariant::factory()->count(3)->create();

    $this->get(route('shop'))->assertOk();
});

test('product page renders for an active product', function () {
    $product = Product::factory()->create();
    ProductVariant::factory()->for($product)->create();

    $this->get(route('product.show', $product))
        ->assertOk()
        ->assertSee($product->name);
});

test('craft page renders', function () {
    $this->get(route('craft'))->assertOk();
});

test('login page renders for guests', function () {
    $this->get(route('login'))->assertOk();
});

test('register page renders for guests', function () {
    $this->get(route('register'))->assertOk();
});

test('cart page renders when empty', function () {
    $this->get(route('cart'))->assertOk();
});

test('cart page renders with items', function () {
    $variant = ProductVariant::factory()->create();
    $sessionId = withGuestCart($variant);

    $this->withCookie(config('session.cookie'), $sessionId)
        ->get(route('cart'))
        ->assertOk()
        ->assertSee($variant->product->name);
});

test('checkout redirects to the cart when the cart is empty', function () {
    $this->get(route('checkout'))->assertRedirect(route('cart'));
});

test('checkout renders for a guest with items in the cart', function () {
    $variant = ProductVariant::factory()->create();
    $sessionId = withGuestCart($variant);

    $this->withCookie(config('session.cookie'), $sessionId)
        ->get(route('checkout'))
        ->assertOk();
});
