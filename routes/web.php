<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Projects\ManageProjects;

Route::redirect('/', 'dashboard')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    // Projects management route - admin only per AD-5
    // This is the standalone route for direct access
    Route::get('/projects', ManageProjects::class)
        ->name('projects')
        ->middleware('can:admin');
});

require __DIR__.'/settings.php';
