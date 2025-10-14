<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\ExportController;



Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware(['auth'])->group(function () {
    Route::get('/', [ProjectController::class, 'index'])->name('home');

    Route::resource('projects', ProjectController::class);
    Route::resource('projects.sections', SectionController::class);
    Route::resource('sections.units', UnitController::class);

    // media
    Route::get('/media', [MediaController::class, 'index'])->name('media.index');
    Route::post('/media', [MediaController::class, 'store'])->name('media.store');

    // export
    Route::post('/export/project/{project}', [ExportController::class, 'exportProject'])->name('export.project');
    Route::post('/export/unit/{unit}', [ExportController::class, 'exportUnit'])->name('export.unit');
});

});

require __DIR__.'/auth.php';
