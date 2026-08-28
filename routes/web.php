<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('/dashboard', \App\Livewire\Dashboard::class)
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

Route::get('/staff', \App\Livewire\Staff\Index::class)
    ->middleware(['auth', 'verified'])
    ->name('staff.index');
Route::get('/attendance/students', \App\Livewire\Attendance\Students::class)
    ->middleware(['auth', 'verified'])
    ->name('attendance.students');

Route::get('/attendance/staff', \App\Livewire\Attendance\Staff::class)
    ->middleware(['auth', 'verified'])
    ->name('attendance.staff');
Route::get('/fees/invoices', \App\Livewire\Fees\Invoices::class)
    ->middleware(['auth', 'verified'])
    ->name('fees.invoices');
Route::get('/payroll', \App\Livewire\Payroll\Index::class)
    ->middleware(['auth', 'verified'])
    ->name('payroll.index');
