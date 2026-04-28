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

use App\Http\Controllers\PaymentController;

Route::get('/payment/success/{order}', [PaymentController::class, 'success'])->name('payment.success');
Route::get('/payment/failure/{order}', [PaymentController::class, 'failure'])->name('payment.failure');
Route::get('/payment/pending/{order}', [PaymentController::class, 'pending'])->name('payment.pending');
Route::post('/payment/webhook', [PaymentController::class, 'webhook'])->name('payment.webhook');

Route::get('/success', function () {
    return view('success');
})->name('success');

Route::get('/cart', function () {
    return view('cart');
})->name('cart');
