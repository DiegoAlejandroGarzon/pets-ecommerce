<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/categories', function () {
    return view('categories');
})->name('categories');

Route::get('/products/{product:slug}', function (\App\Models\Product $product) {
    return view('product-show', ['product' => $product]);
})->name('products.show');

Route::get('/checkout', function () {
    return view('checkout');
})->name('checkout');

Route::get('/success', function () {
    return view('success');
})->name('success');

Route::get('/cart', function () {
    return view('cart');
})->name('cart');
