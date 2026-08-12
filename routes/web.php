<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\VerifyEmailController;


Route::livewire('/', 'pages::home')->name('home');
Route::livewire('/jeans/{product:slug}', 'pages::product')->name('product.show');
Route::livewire('/the-craft-of-denim', 'pages::craft')->name('craft');
Route::livewire('/shop', 'pages::shop')->name('shop');
Route::livewire('/cart', 'pages::cart')->name('cart');
Route::livewire('/checkout', 'pages::checkout')->name('checkout');
Route::livewire('/checkout/success', 'pages::checkout-success')->name('checkout.success');
Route::livewire('/checkout/cancel', 'pages::checkout-cancel')->name('checkout.cancel');
Route::post('/stripe/webhook', StripeWebhookController::class);
// 'guest' middleware means an already-logged-in user gets redirected away
// from login/register rather than seeing those forms again.
Route::livewire('/login', 'pages::login')->name('login')->middleware('guest');
Route::livewire('/register', 'pages::register')->name('register')->middleware('guest');

// 'auth' middleware means anyone not logged in gets redirected to /login
// automatically if they try to visit this page directly.
Route::livewire('/account', 'pages::account')->name('account')->middleware('auth');

Route::livewire('/verify-email', 'pages::verify-email')
    ->name('verification.notice')
    ->middleware('auth');

// The route name 'verification.verify' is required exactly as-is —
// Laravel's built-in VerifyEmail notification generates its link using
// this specific route name, so it must match or the emailed link breaks.
// 'signed' middleware rejects any URL whose signature doesn't match
// (i.e. someone trying to guess or tamper with a verification link).
// 'throttle:6,1' limits it to 6 attempts per minute per IP, guarding
// against someone brute-forcing verification links.
Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['auth', 'signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::livewire('/profile', 'pages::profile')->name('profile')->middleware('auth');
Route::livewire('/profile/edit', 'pages::profile-edit')->name('profile.edit')->middleware('auth');

Route::livewire('/account/orders', 'pages::orders')->name('orders')->middleware('auth');
Route::livewire('/account/orders/{order}', 'pages::order-detail')->name('orders.show')->middleware('auth');

Route::livewire('/forgot-password', 'pages::forgot-password')->name('password.request')->middleware('guest');

// Route name MUST be exactly 'password.reset' — Laravel's built-in
// ResetPassword notification generates its emailed link using this
// specific route name, so it has to match or the link breaks.
Route::livewire('/reset-password/{token}', 'pages::reset-password')->name('password.reset')->middleware('guest');