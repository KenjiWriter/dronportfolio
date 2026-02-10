<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

// Public Routes
Route::get('/', [\App\Http\Controllers\Public\HomeController::class, 'index'])->name('home');
Route::post('/contact', [\App\Http\Controllers\Public\ContactController::class, 'store'])->name('contact.store');
Route::get('/project/{slug}', [\App\Http\Controllers\Public\ProjectController::class, 'show'])->name('project.show');

// Admin Routes
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::resource('projects', \App\Http\Controllers\Admin\ProjectController::class);

    Route::get('/leads', [\App\Http\Controllers\Admin\LeadController::class, 'index'])->name('leads.index');
    Route::patch('/leads/{lead}', [\App\Http\Controllers\Admin\LeadController::class, 'update'])->name('leads.update');
});

require __DIR__ . '/settings.php';
