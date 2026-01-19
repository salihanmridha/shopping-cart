<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware(['auth'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('profile', 'profile')->name('profile');
    Route::view('products', 'products')->name('products');
    Route::view('cart', 'cart')->name('cart');
});

require __DIR__.'/auth.php';
