<?php

use App\Http\Controllers\LocationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\FoodLogController;
use App\Http\Controllers\ExerciseLogController;
use App\Http\Controllers\HealthScoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    // Food Tracker Routes
    Route::get('/tracker-makanan', [FoodLogController::class, 'index'])->name('food-logs.index');
    Route::post('/tracker-makanan', [FoodLogController::class, 'store'])->name('food-logs.store');
    Route::delete('/tracker-makanan/{foodLog}', [FoodLogController::class, 'destroy'])->name('food-logs.destroy');

    // Exercise Tracker Routes
    Route::get('/tracker-olahraga', [ExerciseLogController::class, 'index'])->name('exercise-logs.index');
    Route::post('/tracker-olahraga', [ExerciseLogController::class, 'store'])->name('exercise-logs.store');
    Route::delete('/tracker-olahraga/{exerciseLog}', [ExerciseLogController::class, 'destroy'])->name('exercise-logs.destroy');

    // Overall Health Score Routes
    Route::get('/skor-kesehatan', [HealthScoreController::class, 'index'])->name('health-scores.index');
    Route::post('/skor-kesehatan/generate', [HealthScoreController::class, 'generate'])->name('health-scores.generate');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
});

Route::get('/locations', [LocationController::class, 'index'])->name('locations.index');
Route::post('/locations', [LocationController::class, 'store'])->name('locations.store');

require __DIR__.'/auth.php';