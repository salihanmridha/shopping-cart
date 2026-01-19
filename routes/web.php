<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('products', 'products')->name('products');

Route::middleware(['auth'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('profile', 'profile')->name('profile');
    Route::view('cart', 'cart')->name('cart');
});

require __DIR__.'/auth.php';
