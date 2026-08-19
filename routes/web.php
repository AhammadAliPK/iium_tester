<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', 'dashboard')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    // Projects management route - admin only per AD-5
    Route::view('projects', 'projects')
        ->name('projects')
        ->middleware('can:admin');

    // Team management route - admin only per AD-5
    Route::view('users', 'users')
        ->name('users')
        ->middleware('can:admin');
});

require __DIR__.'/settings.php';
