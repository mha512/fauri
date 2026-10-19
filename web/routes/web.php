<?php

use App\Livewire\Dashboard;
use App\Livewire\Customers;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::get('dashboard', Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('customers', Customers::class)
    ->middleware(['auth', 'verified'])
    ->name('customers');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
