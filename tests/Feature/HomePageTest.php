<?php

use App\Models\Product;

test('homepage renders when there are no products at all', function () {
    $this->get(route('home'))->assertOk();
});

test('homepage renders when no product is active', function () {
    Product::factory()->draft()->create();
    Product::factory()->archived()->create();

    $this->get(route('home'))->assertOk();
});

test('homepage shows the featured product fabric details when a product is active', function () {
    Product::factory()->create([
        'weight_oz' => 14.5,
        'composition' => '100% Cotton',
        'weave' => '3x1 Right-Hand Twill',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('14.5oz 100% Cotton denim, woven with a', false)
        ->assertSee('3x1 right-hand twill', false);
});
