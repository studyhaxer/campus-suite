<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/campuses', \App\Livewire\Campuses\Index::class)
    ->middleware(['auth', 'verified'])
    ->name('campuses.index');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';

Route::get('/academics/classes', \App\Livewire\Academics\ClassSections::class)
    ->middleware(['auth', 'verified'])
    ->name('academics.classes');

Route::get('/students', \App\Livewire\Students\Index::class)
    ->middleware(['auth', 'verified'])
    ->name('students.index');

Route::get('/users', \App\Livewire\Users\Index::class)
    ->middleware(['auth', 'verified'])
    ->name('users.index');