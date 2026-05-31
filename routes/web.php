<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

// Public Routes
Route::get('/', [\App\Http\Controllers\Public\HomeController::class, 'index'])->name('home');
Route::get('/projects', [\App\Http\Controllers\Public\ProjectsController::class, 'index'])->name('projects.index');
Route::post('/contact', [\App\Http\Controllers\Public\ContactController::class, 'store'])->name('contact.store');
Route::get('/project/{slug}', [\App\Http\Controllers\Public\ProjectController::class, 'show'])->name('project.show');
// On-demand media endpoint (called via fetch when a portfolio card is clicked)
Route::get('/api/projects/{slug}/media', [\App\Http\Controllers\Public\ProjectMediaController::class, 'index'])->name('projects.media');
Route::get('/api/projects', [\App\Http\Controllers\Public\ProjectsController::class, 'apiIndex'])->name('projects.api');

// Admin Routes
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::resource('projects', \App\Http\Controllers\Admin\ProjectController::class);
    Route::patch('/projects/{project}/featured', [\App\Http\Controllers\Admin\ProjectController::class, 'toggleFeatured'])->name('projects.toggle-featured');

    Route::get('/project-types', [\App\Http\Controllers\Admin\ProjectTypeController::class, 'index'])->name('project-types.index');
    Route::post('/project-types', [\App\Http\Controllers\Admin\ProjectTypeController::class, 'store'])->name('project-types.store');
    Route::patch('/project-types/{projectType}', [\App\Http\Controllers\Admin\ProjectTypeController::class, 'update'])->name('project-types.update');
    Route::delete('/project-types/{projectType}', [\App\Http\Controllers\Admin\ProjectTypeController::class, 'destroy'])->name('project-types.destroy');

    Route::delete('/project-media/{projectMedia}', [\App\Http\Controllers\Admin\ProjectMediaController::class, 'destroy'])->name('project-media.destroy');

    Route::get('/leads', [\App\Http\Controllers\Admin\LeadController::class, 'index'])->name('leads.index');
    Route::patch('/leads/{lead}', [\App\Http\Controllers\Admin\LeadController::class, 'update'])->name('leads.update');
    Route::delete('/leads/{lead}', [\App\Http\Controllers\Admin\LeadController::class, 'destroy'])->name('leads.destroy');
});

require __DIR__ . '/settings.php';
