<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::home')->name('home');
Route::livewire('/jeans/{product:slug}', 'pages::product')->name('product.show');
Route::livewire('/the-craft-of-denim', 'pages::craft')->name('craft');
Route::livewire('/shop', 'pages::shop')->name('shop');
