<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StripeWebhookController;


Route::livewire('/', 'pages::home')->name('home');
Route::livewire('/jeans/{product:slug}', 'pages::product')->name('product.show');
Route::livewire('/the-craft-of-denim', 'pages::craft')->name('craft');
Route::livewire('/shop', 'pages::shop')->name('shop');
Route::livewire('/cart', 'pages::cart')->name('cart');
Route::livewire('/checkout', 'pages::checkout')->name('checkout');
Route::livewire('/checkout/success', 'pages::checkout-success')->name('checkout.success');
Route::livewire('/checkout/cancel', 'pages::checkout-cancel')->name('checkout.cancel');
Route::post('/stripe/webhook', StripeWebhookController::class);
